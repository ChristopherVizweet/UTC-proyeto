<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('roles')
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->string('search')->trim();

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('apellidoPaterno', 'like', "%{$search}%")
                            ->orWhere('apellidoMaterno', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $request->filled('role'),
                fn ($query) => $query->role($request->role)
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules($request)
        );

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request
                ->file('photo')
                ->store('users', 'public');
        }

        try {
            DB::transaction(function () use (
                $validated,
                $photoPath
            ) {
                $user = User::create([
                    'name' => $validated['name'],
                    'apellidoPaterno' => $validated['apellidoPaterno'],
                    'apellidoMaterno' => $validated['apellidoMaterno'] ?? null,
                    'username' => $validated['username'],
                    'email' => $validated['email'] ?? null,
                    'password' => $validated['password'],
                    'birth_date' => $validated['birth_date'] ?? null,
                    'photo_path' => $photoPath,
                ]);

                $user->syncRoles([
                    $validated['role'],
                ]);

                $this->saveAddress($user, $validated);
                $this->saveProfile($user, $validated);
            });
        } catch (Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function show(User $user): View
    {
        $user->load([
            'roles',
            'address',
            'studentProfile',
            'teacherProfile',
        ]);

        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $user->load([
            'roles',
            'address',
            'studentProfile',
            'teacherProfile',
        ]);

        return view('users.edit', compact('user'));
    }

    public function update(
        Request $request,
        User $user
    ): RedirectResponse {
        $validated = $request->validate(
            $this->rules($request, $user)
        );

        $newPhotoPath = null;
        $oldPhotoPath = $user->photo_path;

        if ($request->hasFile('photo')) {
            $newPhotoPath = $request
                ->file('photo')
                ->store('users', 'public');
        }

        try {
            DB::transaction(function () use (
                $validated,
                $user,
                $newPhotoPath
            ) {
                $userData = [
                    'name' => $validated['name'],
                    'apellidoPaterno' => $validated['apellidoPaterno'],
                    'apellidoMaterno' => $validated['apellidoMaterno'] ?? null,
                    'username' => $validated['username'],
                    'email' => $validated['email'] ?? null,
                    'birth_date' => $validated['birth_date'] ?? null,
                ];

                if (! empty($validated['password'])) {
                    $userData['password'] =
                        $validated['password'];
                }

                if ($newPhotoPath) {
                    $userData['photo_path'] =
                        $newPhotoPath;
                }

                $user->update($userData);

                $user->syncRoles([
                    $validated['role'],
                ]);

                $this->saveAddress($user, $validated);
                $this->saveProfile($user, $validated);
            });
        } catch (Throwable $exception) {
            if ($newPhotoPath) {
                Storage::disk('public')->delete(
                    $newPhotoPath
                );
            }

            throw $exception;
        }

        if ($newPhotoPath && $oldPhotoPath) {
            Storage::disk('public')->delete(
                $oldPhotoPath
            );
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user, Request $request): RedirectResponse
    {
        if ($user->is($request->user())) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    'No puedes eliminar tu propia cuenta.'
                );
        }

        if (
            $user->hasRole('administrador')
            && User::role('administrador')->count() <= 1
        ) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    'No se puede eliminar al único administrador.'
                );
        }

        if ($user->submissions()->exists()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'No se puede eliminar al estudiante porque tiene entregas registradas.');
        }

        if ($user->activityAttempts()->exists()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'No se puede eliminar al estudiante porque tiene intentos de actividades registrados.');
        }

        if ($user->createdActivities()->exists()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'No se puede eliminar al usuario porque creó actividades.');
        }

        if ($user->teachingAssignments()->whereHas('activities')->exists()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'No se puede eliminar al docente porque sus asignaciones tienen actividades.');
        }

        $photoPath = $user->photo_path;

        DB::transaction(function () use ($user) {
            $user->delete();
        });

        if ($photoPath) {
            Storage::disk('public')->delete($photoPath);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    private function rules(
        Request $request,
        ?User $user = null
    ): array {
        $requiresPersonalData = in_array(
            $request->input('role'),
            ['estudiante', 'docente'],
            true
        );

        $requiresEmail = in_array(
            $request->input('role'),
            ['administrador', 'docente'],
            true
        );

        return [
            'role' => [
                'required',
                Rule::in([
                    'administrador',
                    'docente',
                    'estudiante',
                ]),
            ],

            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'apellidoPaterno' => [
                'required',
                'string',
                'max:100',
            ],
            'apellidoMaterno' => [
                'nullable',
                'string',
                'max:100',
            ],
            'username' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('users', 'username')
                    ->ignore($user?->id),
            ],
            'email' => [
                Rule::requiredIf($requiresEmail),
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user?->id),
            ],
            'password' => [
                $user ? 'nullable' : 'required',
                'confirmed',
                Password::min(8),
            ],
            'birth_date' => [
                Rule::requiredIf($requiresPersonalData),
                'nullable',
                'date',
                'before:today',
            ],
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'calle_usuario' => [
                Rule::requiredIf($requiresPersonalData),
                'nullable',
                'string',
                'max:150',
            ],
            'colonia_usuario' => [
                Rule::requiredIf($requiresPersonalData),
                'nullable',
                'string',
                'max:150',
            ],
            'exterior_usuario' => [
                Rule::requiredIf($requiresPersonalData),
                'nullable',
                'string',
                'max:20',
            ],
            'interior_usuario' => [
                'nullable',
                'string',
                'max:20',
            ],
            'municipio_usuario' => [
                Rule::requiredIf($requiresPersonalData),
                'nullable',
                'string',
                'max:150',
            ],
            'estado_usuario' => [
                Rule::requiredIf($requiresPersonalData),
                'nullable',
                'string',
                'max:100',
            ],
            'cp_usuario' => [
                Rule::requiredIf($requiresPersonalData),
                'nullable',
                'digits:5',
            ],

            'nombre_tutor' => [
                'required_if:role,estudiante',
                'nullable',
                'string',
                'max:150',
            ],
            'parentesco_tutor' => [
                'required_if:role,estudiante',
                'nullable',
                'string',
                'max:100',
            ],
            'telefono_tutor' => [
                'required_if:role,estudiante',
                'nullable',
                'string',
                'max:20',
            ],
            'telefonoSecundario_tutor' => [
                'nullable',
                'string',
                'max:20',
            ],

            'telefono_profesor' => [
                'required_if:role,docente',
                'nullable',
                'string',
                'max:20',
            ],
            'nivelEducativo_profesor' => [
                'required_if:role,docente',
                'nullable',
                'string',
                'max:150',
            ],
            'cedula_profesor' => [
                'required_if:role,docente',
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }

    private function saveAddress(
        User $user,
        array $validated
    ): void {
        $hasAddress = in_array(
            $validated['role'],
            ['estudiante', 'docente'],
            true
        );

        if (! $hasAddress) {
            return;
        }

        $user->address()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'calle_usuario' => $validated['calle_usuario'],
                'colonia_usuario' => $validated['colonia_usuario'],
                'exterior_usuario' => $validated['exterior_usuario'],
                'interior_usuario' => $validated['interior_usuario'] ?? null,
                'municipio_usuario' => $validated['municipio_usuario'],
                'estado_usuario' => $validated['estado_usuario'],
                'cp_usuario' => $validated['cp_usuario'],
            ]
        );
    }

    private function saveProfile(
        User $user,
        array $validated
    ): void {
        if ($validated['role'] === 'estudiante') {
            $user->studentProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nombre_tutor' => $validated['nombre_tutor'],
                    'parentesco_tutor' => $validated['parentesco_tutor'],
                    'telefono_tutor' => $validated['telefono_tutor'],
                    'telefonoSecundario_tutor' => $validated['telefonoSecundario_tutor']
                            ?? null,
                ]
            );

            $user->teacherProfile()->delete();

            return;
        }

        if ($validated['role'] === 'docente') {
            $user->teacherProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'telefono_profesor' => $validated['telefono_profesor'],
                    'nivelEducativo_profesor' => $validated['nivelEducativo_profesor'],
                    'cedula_profesor' => $validated['cedula_profesor'],
                ]
            );

            $user->studentProfile()->delete();

            return;
        }

        $user->studentProfile()->delete();
        $user->teacherProfile()->delete();
    }
}

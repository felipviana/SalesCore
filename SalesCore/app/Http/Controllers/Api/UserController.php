<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::orderBy('name')->get();

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'active' => ['boolean'],
        ]);

        $user = User::create($validated);

        return response()->json([
            'message' => 'Usuário cadastrado com sucesso.',
            'user' => $user,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'active' => ['boolean'],
        ]);

        if (empty($validated['password'])) {
            unset($validated[  'password']);
        }

        $user->upodate($validated);

        return response()->json([
            'message' => 'Usuário atualizado com sucesso.',
            'user' => $user,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        if($request->user()->id === $user->id){
            return response()->json([
                'message' => 'Você não pode excluir o próprio usuário logado.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuário removido com sucesso.',
        ]);
    }
}

<?php

namespace App\DTO;

use App\Http\Requests\RegisterUserRequest;

final class RegisterUserDTO
{
	public function __construct(
		public string $name,
		public string $email,
		public string $password,
		public string $role = 'client',
	) {
	}

	public static function fromRequest(RegisterUserRequest $request): self
	{
		$validated = $request->validated();

		return self::fromArray($validated);
	}


	public static function fromArray(array $data): self
	{
		return new self(
			name: trim($data['name']),
			email: strtolower(trim($data['email'])),
			password: $data['password'],
			role: $data['role'] ?? 'client',
		);
	}


	public function toArray(): array
	{
		return [
			'name' => $this->name,
			'email' => $this->email,
			'password' => $this->password,
			'role' => $this->role,
		];
	}
}

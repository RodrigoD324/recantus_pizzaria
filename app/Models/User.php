<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['login', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements HasName
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuario';

    protected $fillable = [
        'id_pessoa',
        'id_usuario_tipo',
        'login',
        'password',
        'id_cancelamento',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function username()
    {
        return 'login';
    }

    public function getFilamentName(): string
    {
        return (string) ($this->login ?? $this->id ?? 'Usuário Sem Nome');
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'id_pessoa');
    }

    public function contato()
    {
        return $this->hasOneThrough(Contato::class, Pessoa::class, 'id', 'id_pessoa', 'id_pessoa', 'id');
    }

    public function endereco()
    {
        return $this->hasOneThrough(Endereco::class, Pessoa::class, 'id', 'id_pessoa', 'id_pessoa', 'id');
    }
}

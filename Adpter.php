<?php

namespace App\Services;

use InvalidArgumentException;

class SuapAdapter {
    private array $data = [];
    private ?string $token = null;

    private const CAMPOS_PERMITIDOS = [
        'matricula',
        'nome_usual',
        'nome',
        'email',
        'tipo_vinculo',
        'curso',
        'semestre',
        'campus',
        'url_foto',
        'foto',
        'turno'
    ];

    public function input_data(array $payload_bruto, ?string $token = null): void {
        $this->data = array_intersect_key(
            $payload_bruto, 
            array_flip(self::CAMPOS_PERMITIDOS)
        );

        if ($token !== null) {
            $this->token = $token;
        }
    }

    public function validate_token(?string $token = null): bool {
        $tokenToValidate = $token ?? $this->token;

        if (empty($tokenToValidate)) {
            return false;
        }

        $tokenParts = explode('.', $tokenToValidate);
        if (count($tokenParts) === 3) {
            $payload = json_decode(base64_decode($tokenParts[1]), true);
            
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return false;
            }
            return true;
        }

        return false;
    }

    public function validate_data(?array $required_keys = null): bool {
        //Valores necessarios caso o controller não especifique (ha se pensar nestes valores)
        $keysToValidate = $required_keys ?? ['matricula', 'email', 'tipo_vinculo'];

        foreach ($keysToValidate as $key) {
            if (empty($this->data[$key])) {
                return false;
            }
        }

        return true;
    }

    
    // Valida a matrícula e traduz as chaves do SUAP para as colunas do Sisref.
    
    public function get_data(): array {
        if (empty($this->data['matricula'])) {
            throw new InvalidArgumentException("Matrícula não encontrada!");
        }

        $matricula = (string) $this->data['matricula'];

        if (!preg_match('/^[0-9]{10,15}$/', $matricula)) {
            throw new InvalidArgumentException("Matrícula inválida recebida: {$matricula}");
        }

        return [
            // Dados para o Model User
            'email'        => $this->data['email'] ?? null,
            'type'         => $this->data['tipo_vinculo'] ?? null,
            
            // Dados de ambos os Models
            'name'         => $this->data['nome_usual'] ?? $this->data['nome'] ?? null,
            'campus_id'    => $this->data['campus'] ?? null,
            
            // Dados para o Model Student
            'mat'          => $matricula,
            'course_id'    => $this->data['curso'] ?? null,
            'semRegular'   => $this->data['semestre'] ?? null,
            'shift_id'     => $this->data['turno'] ?? null,
            'photo'        => $this->data['url_foto'] ?? $this->data['foto'] ?? null,
        ];
    }
}
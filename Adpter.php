<?php

class SuapAdapter {
    private array $data = [];
    private ?string $token = null;

    /**
     * Recebe e armazena os dados vindos do client SUAP.
     * 
     * @param array $data O payload de dados do usuário/autenticação.
     * @param string|null $token O token de acesso opcional, caso enviado junto.
     */
    public function input_data(array $data, ?string $token = null): void {
        $this->data = $data;
        if ($token !== null) {
            $this->token = $token;
        }
    }

    /**
     * Valida se os dados recebidos contêm as chaves obrigatórias para sua aplicação.
     * 
     * @param array $required_keys Lista de chaves esperadas (ex: ['matricula', 'email']).
     * @return bool Retorna true se todos os campos necessários estiverem presentes.
     */
    public function validate_data(array $required_keys): bool {
        if (empty($this->data)) {
            return false;
        }

        foreach ($required_keys as $key) {
            if (!array_key_exists($key, $this->data)) {
                return false;
            }
        }

        return true;
    }

}
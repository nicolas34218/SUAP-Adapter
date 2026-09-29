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

    /**
     * Valida o token de acesso recebido do SUAP.
     * 
     * @param string|null $token O token para validação (se não passado, usa o da classe).
     * @return bool
     */
    public function validate_token(?string $token = null): bool {
        $tokenToValidate = $token ?? $this->token;

        if (empty($tokenToValidate)) {
            return false;
        }

        $tokenParts = explode('.', $tokenToValidate);
        if (count($tokenParts) === 3) {
            $payload = json_decode(base64_decode($tokenParts[1]), true);
            
            // Verifica se o token expirou
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return false;
            }
            
            return true;
        }

        return false;
    }

    public function get_data(): array {
        return $this->data;
    }
}
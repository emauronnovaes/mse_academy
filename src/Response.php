<?php
declare(strict_types=1);

/** Responde em JSON e encerra a execução. */
function mse_json(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Atalho para respostas de erro. */
function mse_error(string $message, int $status = 400): void
{
    mse_json(['error' => $message], $status);
}

/** Lê o corpo JSON da requisição (POST) com segurança. */
function mse_input(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

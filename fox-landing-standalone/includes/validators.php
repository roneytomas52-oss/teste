<?php

declare(strict_types=1);

function only_digits(string $value): string
{
    return preg_replace('/\D+/', '', $value) ?? '';
}

function is_valid_cnpj(string $cnpj): bool
{
    $cnpj = only_digits($cnpj);

    if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
        return false;
    }

    $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $sum += ((int) $cnpj[$i]) * $weights1[$i];
    }
    $digit1 = $sum % 11;
    $digit1 = $digit1 < 2 ? 0 : 11 - $digit1;

    $sum = 0;
    for ($i = 0; $i < 13; $i++) {
        $sum += ((int) $cnpj[$i]) * $weights2[$i];
    }
    $digit2 = $sum % 11;
    $digit2 = $digit2 < 2 ? 0 : 11 - $digit2;

    return ((int) $cnpj[12] === $digit1) && ((int) $cnpj[13] === $digit2);
}

function is_valid_cpf(string $cpf): bool
{
    $cpf = only_digits($cpf);

    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += ((int) $cpf[$i]) * (10 - $i);
    }
    $digit1 = ($sum * 10) % 11;
    $digit1 = $digit1 === 10 ? 0 : $digit1;

    $sum = 0;
    for ($i = 0; $i < 10; $i++) {
        $sum += ((int) $cpf[$i]) * (11 - $i);
    }
    $digit2 = ($sum * 10) % 11;
    $digit2 = $digit2 === 10 ? 0 : $digit2;

    return ((int) $cpf[9] === $digit1) && ((int) $cpf[10] === $digit2);
}

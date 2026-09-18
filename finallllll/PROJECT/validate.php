<?php
declare(strict_types=1);

/**
 * Shared registration validation rules.
 */
$validates = [
    'first_name' => [
        'filters' => FILTER_VALIDATE_REGEXP,
        'my_options' => ['options' => ['regexp' => '/^[\p{L}]{2,50}$/u']],
        'error' => 'الاسم الأول لازم يكون من 2 إلى 50 حرفًا.'
    ],
    'last_name' => [
        'filters' => FILTER_VALIDATE_REGEXP,
        'my_options' => ['options' => ['regexp' => '/^[\p{L}]{2,50}$/u']],
        'error' => 'الاسم الأخير لازم يكون من 2 إلى 50 حرفًا.'
    ],
    'email' => [
        'filters' => FILTER_VALIDATE_EMAIL,
        'error' => 'البريد الإلكتروني غير صحيح.'
    ],
    'password' => [
        'filters' => FILTER_VALIDATE_REGEXP,
        'my_options' => ['options' => ['regexp' => '/^.{6,30}$/s']],
        'error' => 'الباسورد لازم يكون من 6 إلى 30 حرفًا.'
    ],
];

function apply_validation_rules(array $data, array $rules): array
{
    $errors = [];

    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? '';

        if ($value === '' || filter_var($value, $rule['filters'], $rule['my_options'] ?? []) === false) {
            $errors[$field] = $rule['error'];
        }
    }

    return $errors;
}

function validate_password_match(string $password, string $confirm): ?string
{
    return $password !== $confirm ? 'الباسورد وتأكيده غير متطابقين.' : null;
}

function email_exists(mysqli $conn, string $email): bool
{
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();

    return $exists;
}

function validate_registration_form(mysqli $conn, array $data): array
{
    global $validates;

    $errors = array_values(apply_validation_rules($data, $validates));

    $passwordError = validate_password_match(
        (string)($data['password'] ?? ''),
        (string)($data['confirm_password'] ?? '')
    );

    if ($passwordError !== null) {
        $errors[] = $passwordError;
    }

    if (!isset($data['email']) || filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        if (email_exists($conn, (string)($data['email'] ?? ''))) {
            $errors[] = 'البريد الإلكتروني مستخدم بالفعل.';
        }
    }

    return array_values(array_unique($errors));
}

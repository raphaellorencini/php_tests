<?php

#[Attribute(Attribute::TARGET_PROPERTY)]
class MinLength {
    public int $length;
    public string $message;

    public function __construct(int $length, string $message) {
        $this->length = $length;
        $this->message = $message;
    }

    public function validate(string|null $value): bool {
        return strlen($value) >= $this->length;
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class Email {
    public string $message;

    public function __construct(string $message) {
        $this->message = $message;
    }

    public function validate(string|null $value): bool {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class UserRole {
    public array $allowedRoles;

    public function __construct(string ...$allowedRoles) {
        $this->allowedRoles = $allowedRoles;
    }

    public function validate(string|null $value): bool {
        return in_array($value, $this->allowedRoles);
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class PriceRange {
    public float $min;
    public float $max;
    public string $message;

    public function __construct(float $min, float $max, string $message) {
        $this->min = $min;
        $this->max = $max;
        $this->message = $message;
    }

    public function validate(float|null $value): bool {
        return $value >= $this->min && $value <= $this->max;
    }
}

trait ValidateAttributes {
    public function validateAttributes(): array {
        $validationErrors = [];
        $class = new ReflectionClass($this);

        foreach ($class->getProperties() as $property) {
            $reflectionProperty = $class->getProperty($property->name);
            $reflectionProperty->setAccessible(true);

            foreach ($reflectionProperty->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();

                if (method_exists($instance, 'validate')) {
                    $propertyValue = $reflectionProperty->getValue($this);

                    $isValid = $instance->validate($propertyValue);
                    if (!$isValid) {
                        $validationErrors[] = $attribute->getArguments()['message'] ?? 'Erro de validação não especificado.';
                    }
                }
            }
        }

        return $validationErrors;
    }
}

class User {
    use ValidateAttributes;

    #[MinLength(length: 3, message: 'O nome deve ter pelo menos 3 caracteres.')]
    public string $name;

    #[Email(message: 'O e-mail não é válido.')]
    public string $email;

    #[UserRole('admin', 'user', message: 'A função do usuário deve ser admin ou user.')]
    public string $role;

    public function __construct(string $name, string $email, string $role) {
        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
    }

    public function canEditProduct(): bool {
        return $this->role === 'admin';
    }
}

class Product {
    use ValidateAttributes;

    #[MinLength(5, message: 'O nome do produto deve ter pelo menos 5 caracteres.')]
    public string $productName;

    #[PriceRange(0.01, 1000, message: 'O preço deve estar entre 0,01 centavos e 1000.')]
    public float $price;

    public function __construct(string $productName, float $price) {
        $this->productName = $productName;
        $this->price = $price;
    }

    public function canBeEditedByUser(User $user): bool {
        return $user->canEditProduct();
    }
}

function testUserProduct(User $user, Product $product) {
    $validationErrorsUser = $user->validateAttributes();
    if (!empty($validationErrorsUser)) {
        foreach ($validationErrorsUser as $error) {
            echo 'Erro de validação em usuário: ' . $error . PHP_EOL;
        }
    }

    $validationErrorsProduct = $product->validateAttributes();
    if (!empty($validationErrorsProduct)) {
        foreach ($validationErrorsProduct as $error) {
            echo 'Erro de validação em produto: ' . $error . PHP_EOL;
        }
    }

    if ($user->canEditProduct()) {
        echo 'Usuário pode editar produtos.' . PHP_EOL;
    } else {
        echo 'Usuário não pode editar produtos.' . PHP_EOL;
    }

    if ($product->canBeEditedByUser($user)) {
        echo 'Usuário pode editar este produto.' . PHP_EOL;
    } else {
        echo 'Usuário não pode editar este produto.' . PHP_EOL;
    }

}

// Criando um usuário e um produto
$user = new User('Admin', 'admin@example.com', 'admin');
$product = new Product('Produto A', 50.00);
testUserProduct($user, $product);

echo "######################\n";

$user2 = new User('User Test', 'user%test.com', 'undefined');
$product2 = new Product('p', -1);
testUserProduct($user2, $product2);


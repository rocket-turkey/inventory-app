<?php
declare(strict_types=1);

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

final class InputValidator
{
    public function product(array $input): array
    {
        $sku = trim((string)($input['sku'] ?? ''));
        $name = trim((string)($input['name'] ?? ''));
        $price = (string)($input['price'] ?? '');
        $reorder = filter_var($input['reorder_level'] ?? null, FILTER_VALIDATE_INT);
        $validator = Validation::createValidator();
        $valid = count($validator->validate($sku, [new Assert\NotBlank(), new Assert\Length(max: 64)])) === 0
            && count($validator->validate($name, [new Assert\NotBlank(), new Assert\Length(max: 160)])) === 0
            && count($validator->validate($price, new Assert\Regex('/^\d{1,8}(\.\d{1,2})?$/'))) === 0
            && $reorder !== false
            && count($validator->validate($reorder, new Assert\GreaterThanOrEqual(0))) === 0;
        if (!$valid) {
            throw new InvalidArgumentException('Enter a SKU, name, valid price, and nonnegative reorder level.');
        }
        return [$sku, $name, $price, $reorder];
    }

    public function stock(array $input): array
    {
        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
        $change = filter_var($input['change_amount'] ?? null, FILTER_VALIDATE_INT);
        $note = trim((string)($input['note'] ?? ''));
        $validator = Validation::createValidator();
        if ($id === false || $id < 1 || $change === false ||
            count($validator->validate($change, new Assert\NotEqualTo(0))) !== 0 ||
            count($validator->validate($note, [new Assert\NotBlank(), new Assert\Length(max: 255)])) !== 0) {
            throw new InvalidArgumentException('Enter a nonzero stock change and a note.');
        }
        return [$id, $change, $note];
    }
}

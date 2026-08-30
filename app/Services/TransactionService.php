<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\ExpenseModel;

final class ExpenseService
{
    public function __construct(
        private Database $database,
        private ExpenseModel $expenseModel
    ) {}

    public function createExpense(
        int $categoryId,
        float $amount,
        string $date,
        ?string $note
    ): int {
        if ($categoryId <= 0) {
            throw new \InvalidArgumentException(
                'Invalid category.'
            );
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException(
                'Amount must be greater than zero.'
            );
        }

        $this->database->beginExpense();

        try {
            $expenseId = $this->expenseModel->create([
                'category_id' => $categoryId,
                'amount' => $amount,
                'expense_date' => $date,
                'note' => $note,
            ]);

            /*
             * Other database operations could happen here.
             */

            $this->database->commit();

            return $expenseId;
        } catch (\Throwable $exception) {
            if ($this->database->inExpense()) {
                $this->database->rollBack();
            }

            throw $exception;
        }
    }
}

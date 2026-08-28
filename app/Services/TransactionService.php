<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\TransactionModel;

final class TransactionService
{
    public function __construct(
        private Database $database,
        private TransactionModel $transactionModel
    ) {}

    public function createTransaction(
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

        $this->database->beginTransaction();

        try {
            $transactionId = $this->transactionModel->create([
                'category_id' => $categoryId,
                'amount' => $amount,
                'transaction_date' => $date,
                'note' => $note,
            ]);

            /*
             * Other database operations could happen here.
             */

            $this->database->commit();

            return $transactionId;
        } catch (\Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }

            throw $exception;
        }
    }
}

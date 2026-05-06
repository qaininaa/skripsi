<?php

namespace App\Domains\ReportAssignment\DTOs;

/**
 * Data transfer object for report assignment payload.
 */
class ReportAssignmentDTO
{
    public function __construct(
        public readonly string $reportTypeId,
        public readonly string $productName,
        public readonly string $batchNumber,
    ) {}

    /**
     * Create DTO from validated request payload.
     *
     * @param  array{report_type_id: string, product_name: string, batch_number: string}  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            reportTypeId: (string) $validated['report_type_id'],
            productName: (string) $validated['product_name'],
            batchNumber: (string) $validated['batch_number'],
        );
    }

    /**
     * Convert DTO to create payload.
     *
     * @return array{report_type_id: string, product_name: string, batch_number: string, created_by: string}
     */
    public function toCreatePayload(string $createdBy): array
    {
        return [
            'report_type_id' => $this->reportTypeId,
            'product_name' => $this->productName,
            'batch_number' => $this->batchNumber,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Convert DTO to update payload.
     *
     * @return array{report_type_id: string, product_name: string, batch_number: string}
     */
    public function toUpdatePayload(): array
    {
        return [
            'report_type_id' => $this->reportTypeId,
            'product_name' => $this->productName,
            'batch_number' => $this->batchNumber,
        ];
    }
}

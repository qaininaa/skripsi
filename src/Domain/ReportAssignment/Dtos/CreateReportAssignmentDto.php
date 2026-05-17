<?php

namespace Domain\ReportAssignment\Dtos;

/**
 * DTO for creating a new report assignment.
 */
class CreateReportAssignmentDto
{
    public function __construct(
        public readonly string $reportTypeId,
        public readonly string $productName,
        public readonly string $batchNumber,
    ) {}

    /**
     * Build DTO from validated request payload.
     *
     * @param  array<string, mixed>  $validated
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
     * Convert DTO to persistence payload, attaching the creator user id.
     *
     * @return array<string, mixed>
     */
    public function toArray(string $createdBy): array
    {
        return [
            'report_type_id' => $this->reportTypeId,
            'product_name' => $this->productName,
            'batch_number' => $this->batchNumber,
            'created_by' => $createdBy,
        ];
    }
}

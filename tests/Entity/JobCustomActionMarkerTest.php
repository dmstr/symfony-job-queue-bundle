<?php

// file generated with AI assistance: Claude Code - 2026-11-27 00:23:12 UTC

declare(strict_types=1);

namespace Dmstr\SymfonyJobQueue\Tests\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Operation;
use Dmstr\SymfonyJobQueue\Entity\Job;
use Dmstr\SymfonyJobQueue\State\JobPruneProcessor;
use PHPUnit\Framework\TestCase;

/**
 * Regression guard for the `custom_action` marker: api-platform-utils-bundle
 * classifies custom actions only by `extraProperties['custom_action'] === true`
 * and ignores truthy values such as `1` or `'true'`.
 *
 * Only the `custom_action` key is asserted, never the whole extraProperties
 * array, because API Platform may add keys of its own.
 */
final class JobCustomActionMarkerTest extends TestCase
{
    private const MARKER = 'custom_action';

    public function testResourceDeclaresExactlyOnePruneOperation(): void
    {
        $operations = $this->resource()->getOperations();

        self::assertNotNull($operations, 'Job resource declares no operations.');
        self::assertCount(3, $operations, 'Job resource must declare Get, GetCollection and the prune Post.');
        self::assertCount(1, $this->pruneOperations(), \sprintf(
            'Exactly one operation must be named "%s".',
            JobPruneProcessor::OPERATION,
        ));
    }

    public function testPruneOperationCarriesStrictTrueMarker(): void
    {
        $prune = $this->pruneOperations();
        self::assertCount(1, $prune, 'Prune operation not found.');

        self::assertSame(
            true,
            $prune[0]->getExtraProperties()[self::MARKER] ?? null,
            \sprintf('Operation "%s" must carry extraProperties["%s"] === true (not 1 or \'true\').', JobPruneProcessor::OPERATION, self::MARKER),
        );
    }

    public function testOtherOperationsCarryNoMarker(): void
    {
        $checked = 0;
        foreach ($this->resource()->getOperations() ?? [] as $operation) {
            if ($operation->getName() === JobPruneProcessor::OPERATION) {
                continue;
            }
            ++$checked;
            self::assertArrayNotHasKey(
                self::MARKER,
                $operation->getExtraProperties() ?? [],
                \sprintf('Operation "%s" (%s) must not carry the "%s" key, not even as false.', (string) $operation->getName(), $operation::class, self::MARKER),
            );
        }
        self::assertSame(2, $checked, 'Expected Get and GetCollection besides the prune operation.');
    }

    public function testResourceItselfCarriesNoMarker(): void
    {
        // An ApiResource built through reflection does not cascade resource-level
        // extraProperties to its operations, so a marker moved from the prune
        // operation to the resource would slip past the operation tests above.
        self::assertArrayNotHasKey(
            self::MARKER,
            $this->resource()->getExtraProperties() ?? [],
            \sprintf('The "%s" marker belongs on the prune operation, not on the resource.', self::MARKER),
        );
    }

    /**
     * @return list<Operation>
     */
    private function pruneOperations(): array
    {
        $prune = [];
        foreach ($this->resource()->getOperations() ?? [] as $operation) {
            if ($operation->getName() === JobPruneProcessor::OPERATION) {
                $prune[] = $operation;
            }
        }

        return $prune;
    }

    private function resource(): ApiResource
    {
        return (new \ReflectionClass(Job::class))->getAttributes(ApiResource::class)[0]->newInstance();
    }
}

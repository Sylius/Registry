<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Component\Registry;

final class PrioritizedServiceRegistry implements PrioritizedServiceRegistryInterface
{
    /**
     * @psalm-var array<int, array{service: object, priority: int}>
     *
     * @var array
     */
    private $registry = [];

    /** @var bool */
    private $sorted = true;

    /**
     * Interface which is required by all services.
     *
     * @var string
     */
    private $interface;

    /**
     * Human readable context for these services, e.g. "tax calculation"
     *
     * @var string
     */
    private $context;

    public function __construct(string $interface, string $context = 'service')
    {
        $this->interface = $interface;
        $this->context = $context;
    }

    public function all(): iterable
    {
        if ($this->sorted === false) {
            /** @psalm-suppress InvalidPassByReference Doing PHP magic, it works this way */
            array_multisort(
                array_column($this->registry, 'priority'),
                \SORT_DESC,
                array_keys($this->registry),
                \SORT_ASC,
                $this->registry
            );

            $this->sorted = true;
        }

        foreach ($this->registry as $record) {
            yield $record['service'];
        }
    }

    public function register($service, int $priority = 0): void
    {
        $this->assertServiceHaveType($service);

        $this->registry[] = ['service' => $service, 'priority' => $priority];
        $this->sorted = false;
    }

    public function unregister($service): void
    {
        if (!$this->has($service)) {
            throw new NonExistingServiceException(
                $this->context,
                get_class($service),
                array_map('get_class', array_column($this->registry, 'service'))
            );
        }

        $this->registry = array_filter(
            $this->registry,
            static function (array $record) use ($service): bool {
                return $record['service'] !== $service;
            }
        );
    }

    public function has($service): bool
    {
        $this->assertServiceHaveType($service);

        foreach ($this->registry as $record) {
            if ($record['service'] === $service) {
                return true;
            }
        }

        return false;
    }

    private function assertServiceHaveType(object $service): void
    {
        if (!$service instanceof $this->interface) {
            throw new \InvalidArgumentException(sprintf(
                '%s needs to implements "%s", "%s" given.',
                $this->context,
                $this->interface,
                get_class($service)
            ));
        }
    }
}

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

namespace spec\Sylius\Component\Registry;

use PhpSpec\ObjectBehavior;
use spec\Sylius\Component\Registry\Fixture\SampleServiceInterface;
use Sylius\Component\Registry\NonExistingServiceException;
use Sylius\Component\Registry\PrioritizedServiceRegistryInterface;

final class PrioritizedServiceRegistrySpec extends ObjectBehavior
{
    function let(): void
    {
        $this->beConstructedWith(SampleServiceInterface::class);
    }

    function it_implements_prioritized_service_registry_interface(): void
    {
        $this->shouldImplement(PrioritizedServiceRegistryInterface::class);
    }

    function it_initializes_services_priority_queue_by_default(): void
    {
        $this->all()->shouldIterateAs([]);
    }

    function it_keeps_fifo_order_for_registered_services(
        SampleServiceInterface $serviceOne,
        SampleServiceInterface $serviceTwo
    ): void {
        $this->register($serviceOne);
        $this->register($serviceTwo);

        $this->all()->shouldHaveCount(2);
        $this->all()->shouldIterateAs([$serviceOne, $serviceTwo]);
    }

    function it_registers_services_in_the_correct_prioritized_order(
        SampleServiceInterface $serviceOne,
        SampleServiceInterface $serviceTwo,
        SampleServiceInterface $serviceThree
    ): void {
        $this->has($serviceOne)->shouldReturn(false);
        $this->has($serviceTwo)->shouldReturn(false);
        $this->has($serviceThree)->shouldReturn(false);

        $this->register($serviceOne);
        $this->register($serviceTwo, -1);
        $this->register($serviceThree, 1);

        $this->has($serviceOne)->shouldReturn(true);
        $this->has($serviceTwo)->shouldReturn(true);
        $this->has($serviceThree)->shouldReturn(true);

        $this->all()->shouldHaveCount(3);
        $this->all()->shouldIterateAs([$serviceThree, $serviceOne, $serviceTwo]);
    }

    function it_throws_exception_when_trying_to_register_service_without_required_interface(\stdClass $service): void
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->duringRegister($service)
        ;
    }

    function it_throws_exception_when_trying_to_check_for_a_registered_service_without_required_interface(
        \stdClass $service
    ): void {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->duringHas($service)
        ;
    }

    function it_unregisters_service(SampleServiceInterface $service): void
    {
        $this->register($service);
        $this->has($service)->shouldReturn(true);

        $this->unregister($service);
        $this->has($service)->shouldReturn(false);
    }

    function it_throws_exception_if_trying_to_unregister_service_of_non_existing_type(SampleServiceInterface $service): void
    {
        $this
            ->shouldThrow(NonExistingServiceException::class)
            ->duringUnregister($service)
        ;
    }
}

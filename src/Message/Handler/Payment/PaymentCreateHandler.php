<?php
namespace OrderComponent\Payment\Message\Handler\Payment;

use Doctrine\ORM\EntityManagerInterface;
use OrderComponent\Payment\Contract\RepositoryInterface\Payment\PaymentRepositoryInterface;
use OrderComponent\Payment\Contract\ServiceInterface\Payment\PaymentGatewayInterface;
use OrderComponent\Payment\Entity\Payment\Payment;
use OrderComponent\Payment\Message\Command\Payment\PaymentCreateCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PaymentCreateHandler
{
    public function __construct(
        private PaymentRepositoryInterface $repo,
        private EntityManagerInterface $em,
        /** @var iterable<PaymentGatewayInterface> */
        private iterable $gateways
    ) {}

    public function __invoke(PaymentCreateCommand $c): void
    {
        $payment = new Payment(\Ramsey\Uuid\Uuid::uuid4()->toString(), $c->orderId, $c->amountMinor, $c->currency);

        $gateway = $this->selectGateway($c->gatewayCode);
        $txId = $gateway->authorize($payment->id(), $c->amountMinor, $c->currency);

        $this->repo->save($payment);
        // TODO: persist PaymentTransaction(auth) with $txId
        $this->em->flush();
    }

    private function selectGateway(string $code): PaymentGatewayInterface
    {
        foreach ($this->gateways as $g) {
            if (method_exists($g, 'code') && $g->code() === $code) {
                return $g;
            }
        }
        throw new \RuntimeException('Payment gateway not found: ' . $code);
    }
}
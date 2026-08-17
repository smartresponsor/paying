<?php

declare(strict_types=1);

namespace App\Paying\Service\Http\Payment;

use App\Cruding\Dto\Crud\Entrypoint\CrudServiceContext;
use App\Cruding\Provider\Crud\CrudPageDefinitionProvider;
use App\Cruding\Service\Crud\Resource\CrudResourceContractFactory;
use App\Cruding\Value\Resource\CrudResourceContract;
use App\Paying\Dto\Payment\PaymentPlacementFormData;
use App\Paying\Form\PaymentPlacementType;
use App\Paying\ServiceInterface\PaymentStartServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class PaymentNewService
{
    private const SESSION_KEY = 'retail_placement';

    public function __construct(
        private FormFactoryInterface $formFactory,
        private CrudPageDefinitionProvider $pageDefinitionProvider,
        private CrudResourceContractFactory $contractFactory,
        private PaymentStartServiceInterface $paymentStartService,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function get(CrudServiceContext $context): CrudResourceContract|RedirectResponse
    {
        return $this->handle($context);
    }

    public function post(CrudServiceContext $context): CrudResourceContract|RedirectResponse
    {
        return $this->handle($context);
    }

    private function handle(CrudServiceContext $context): CrudResourceContract|RedirectResponse
    {
        $placement = $this->placement($context);
        if (null === $placement) {
            return $this->redirect('retail/new');
        }

        $data = new PaymentPlacementFormData();
        $data->amount = $this->majorAmount($placement['amountMinor']);
        $data->currency = $placement['currency'];

        $form = $this->formFactory->create(PaymentPlacementType::class, $data);
        $form->handleRequest($context->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->paymentStartService->start(
                $placement['orderId'],
                $data->provider,
                $data->amount,
                $data->currency,
                'placement-'.$placement['orderId'].'-'.$placement['shipmentId'],
                'placement',
            );

            $slug = $result->payment->slug();
            $context->request->getSession()->remove(self::SESSION_KEY);

            return $this->redirect('payment/show/'.$slug);
        }

        $formView = $form->createView();

        return $this->contractFactory->create(
            $this->pageDefinitionProvider->provideNew($context->crudContext, $data, $formView),
            $data,
            $formView,
        );
    }

    /** @return array{retailId: string, orderId: string, shipmentId: string, amountMinor: ?int, currency: string}|null */
    private function placement(CrudServiceContext $context): ?array
    {
        if (!$context->request->hasSession()) {
            return null;
        }

        $placement = $context->request->getSession()->get(self::SESSION_KEY);
        if (!is_array($placement)) {
            return null;
        }

        foreach (['retailId', 'orderId', 'shipmentId', 'currency'] as $key) {
            if (!isset($placement[$key]) || !is_scalar($placement[$key]) || '' === trim((string) $placement[$key])) {
                return null;
            }
        }

        $amountMinor = $placement['amountMinor'] ?? null;

        return [
            'retailId' => trim((string) $placement['retailId']),
            'orderId' => trim((string) $placement['orderId']),
            'shipmentId' => trim((string) $placement['shipmentId']),
            'amountMinor' => is_int($amountMinor) ? $amountMinor : (is_numeric($amountMinor) ? (int) $amountMinor : null),
            'currency' => strtoupper(trim((string) $placement['currency'])),
        ];
    }

    private function majorAmount(?int $amountMinor): string
    {
        return number_format(max(0, $amountMinor ?? 0) / 100, 2, '.', '');
    }

    private function redirect(string $crudPath): RedirectResponse
    {
        return new RedirectResponse($this->urlGenerator->generate(
            'cruding_tokenized_catch_all',
            ['crudPath' => $crudPath],
        ));
    }
}

<?php

namespace Eclyptox\SyliusRedsysPlugin\Payum;

use Eclyptox\SyliusRedsysPlugin\Payum\Action\NotifyAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory as CoreGatewayFactory;
use Eclyptox\SyliusRedsysPlugin\Payum\Action\CaptureAction;
use Eclyptox\SyliusRedsysPlugin\Payum\Action\ConvertPaymentAction;
use Eclyptox\SyliusRedsysPlugin\Payum\Action\StatusAction;
use Payum\Core\GatewayFactoryInterface;

class RedsysGatewayFactory implements GatewayFactoryInterface
{
    /**
     * @var GatewayFactoryInterface
     */
    protected $coreGatewayFactory;

    /**
     * @var array
     */
    private $defaultConfig;

    /**
     * @param array $defaultConfig
     * @param GatewayFactoryInterface $coreGatewayFactory
     */
    public function __construct(array $defaultConfig = array(), GatewayFactoryInterface $coreGatewayFactory = null)
    {
        $this->coreGatewayFactory = $coreGatewayFactory ?: new CoreGatewayFactory();
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        return $this->coreGatewayFactory->create($this->createConfig($config));
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);
        $config->defaults($this->coreGatewayFactory->createConfig());

        $config->defaults(array(
            'payum.factory_name' => 'redsys',
            'payum.factory_title' => 'Redsys',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.status' => new StatusAction(),
        ));

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'merchant_code' => '',
                'terminal' => '',
                'secret_key' => '',
                'sandbox' => true,
                'consumer_language' => '',
                'pay_methods' => '',
            );

            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array('merchant_code', 'terminal', 'secret_key', 'consumer_language');

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $redsysConfig = array(
                    'merchant_code' => $config['merchant_code'],
                    'terminal' => $config['terminal'],
                    'secret_key' => $config['secret_key'],
                    'sandbox' => $config['sandbox'],
                    'consumer_language' => $config['consumer_language'],
                    'pay_methods' => $config['pay_methods'],
                );

                return new Api($redsysConfig);
            };
        }

        return (array) $config;
    }
}

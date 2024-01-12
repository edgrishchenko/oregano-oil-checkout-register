<?php declare(strict_types=1);

namespace MagediaCheckoutCreateCustomer\Subscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs;
use Enlight_Template_Manager;
use Enlight_Components_Session_Namespace as Session;

class TemplateRegistration implements SubscriberInterface
{
    /**
     * @var string
     */
    private string $pluginDirectory;

    /**
     * @var Enlight_Template_Manager
     */
    private Enlight_Template_Manager $templateManager;

    /**
     * @param $pluginDirectory
     * @param Enlight_Template_Manager $templateManager
     */
    public function __construct(
        $pluginDirectory,
        Enlight_Template_Manager $templateManager
    )
    {
        $this->pluginDirectory = $pluginDirectory;
        $this->templateManager = $templateManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PreDispatch' => 'onPreDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatch',
            'Theme_Compiler_Collect_Plugin_Javascript' => 'addJsFiles'
        ];
    }

    public function onPreDispatch()
    {
        $this->templateManager->addTemplateDir($this->pluginDirectory . '/Resources/views');
    }

    /**
     * @param Enlight_Controller_ActionEventArgs $args
     * @return void
     */
    public function onPostDispatch(Enlight_Controller_ActionEventArgs $args): void
    {
        $controller = $args->getSubject();
        $request = $controller->Request();

        $controllerList = ['checkout'];
        if (in_array($request->getControllerName(), $controllerList)) {
             $controller->View()->assign('sOneTimeAccount', Shopware()->Session()->offsetGet('sOneTimeAccount'));
        }
    }

    /**
     * Provide the file collection for js files
     *
     * @return ArrayCollection
     */
    public function addJsFiles(): ArrayCollection
    {
        $jsFiles = [
            $this->getPluginPath() . '/Resources/views/frontend/_public/src/js/modal.js',
        ];

        return new ArrayCollection($jsFiles);
    }

    /**
     * @return string
     */
    protected function getPluginPath(): string
    {
        return __DIR__ . '/..';
    }
}
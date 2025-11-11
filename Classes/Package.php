<?php
namespace fucodo\Notification;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use KayStrobach\Backend\Controller\BackendController;
use Neos\Flow\Package\Package as BasePackage;
use Neos\Flow\Configuration\ConfigurationManager;
use fucodo\Notification\Slots\Controller\BackendControllerSlots;

/**
 * The Fluid Package
 *
 */
class Package extends BasePackage {
    /**
     * Invokes custom PHP code directly after the package manager has been initialized.
     *
     * @param \Neos\Flow\Core\Bootstrap $bootstrap The current bootstrap
     * @return void
     */
    public function boot(\Neos\Flow\Core\Bootstrap $bootstrap) : void
    {
        //register Configuration Type Menu
        $dispatcher = $bootstrap->getSignalSlotDispatcher();

        //register Configuration Type Menu
        $dispatcher->connect(
            BackendController::class,
            'beforeTopNavigationIsRendered',
            BackendControllerSlots::class,
            'beforeTopNavigationIsRendered'
        );
    }
}

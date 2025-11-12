<?php

namespace fucodo\Notification\Slots\Controller;

use fucodo\registry\Domain\Repository\RegistryEntryRepository;
use KayStrobach\Backend\Domain\Model\TopNavigation;
use Neos\Flow\Security\Context;
use Neos\Flow\Annotations as Flow;

class BackendControllerSlots
{
    public function beforeTopNavigationIsRendered(TopNavigation $topNavigation)
    {
        $topNavigation->addEntryObject(
            (new TopNavigation\EntryWithTemplate(
                'resource://fucodo.notification/Private/Backend/TopBar/Notification.html',
                'start',
                'top-navigation-entry-fucodo-notification'
            ))
        );
    }
}

<?php

namespace Visca\Bot\Bundle\GitHubBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Visca\Bot\Component\GitHub\Event\Event\WebHookReceivedEvent;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $event = new WebHookReceivedEvent(
            $request->headers->all(),
            json_decode($request->getContent(), true)
        );

        $this
            ->get('simple_bus.asynchronous.event_publisher')
            ->publish($event);

        return new Response("OK");
    }
}

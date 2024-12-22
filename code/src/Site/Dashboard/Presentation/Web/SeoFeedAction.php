<?php

declare(strict_types=1);

namespace Site\Dashboard\Presentation\Web;

use Orders\DomainModel\Service\OrdersInterface;
use Services\DomainModel\Service\ServicesInterface;
use Site\Dashboard\Presentation\Web\Response\FeedAtomHtmlResponder;
use Site\Dashboard\Presentation\Web\Response\FeedRssHtmlResponder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Annotation\Route;

final readonly class SeoFeedAction
{
    public function __construct(
        private OrdersInterface $orders,
        private ServicesInterface $services,
        private Security $security,
    ) {
    }

    #[Route('/feed/rss.xml', name: 'feed-rss', defaults: ['_format' => 'xml'])]
    public function rss(FeedRssHtmlResponder $responder): FeedRssHtmlResponder
    {
        return $responder
            ->context([
                'items' => $this->getItems(),
                'organization' => 'organization',
                'website_url' => 'website_url',
            ])->respond();
    }

    #[Route('/feed/atom.xml', name: 'feed-atom', defaults: ['_format' => 'xml'])]
    public function atom(FeedAtomHtmlResponder $responder): FeedAtomHtmlResponder
    {
        return $responder
            ->context([
                'items' => $this->getItems(),
                'organization' => [
                    'name' => 'sss',
                    'description' => 'sss',
                ],
                'website_url' => 'website_url',
            ])
            ->respond();
    }

    private function getItems(): array
    {
        $user = $this->security->getUser();
        if (!$user) {
            return [];
        }

        $items = [];
        if (in_array('ROLE_CLIENT', $user->getRoles(), true)) {
            $items = $this->orders->last(20);
        } elseif (in_array('ROLE_PARTNER', $user->getRoles(), true)) {
            $items = $this->services->last(20);
        }

        if ([] === $items) {
            $items = [
                ...$this->orders->last(10),
                ...$this->services->last(10),
            ];
        }

        return $items;
    }
}

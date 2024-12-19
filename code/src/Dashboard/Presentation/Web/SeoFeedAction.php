<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use App\Dashboard\Presentation\Web\Response\FeedAtomResponder;
use App\Dashboard\Presentation\Web\Response\FeedRssResponder;
use App\Order\DomainModel\Service\OrdersInterface;
use App\Service\DomainModel\Service\ServicesInterface;
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
    public function rss(FeedRssResponder $responder): FeedRssResponder
    {
        return $responder
            ->context([
                'items' => $this->getItems(),
                'organization' => 'organization',
                'website_url' => 'website_url',
            ])->respond();
    }

    #[Route('/feed/atom.xml', name: 'feed-atom', defaults: ['_format' => 'xml'])]
    public function atom(FeedAtomResponder $responder): FeedAtomResponder
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

        if ($items === []) {
            $items = [
                ...$this->orders->last(10),
                ...$this->services->last(10),
            ];
        }

        return $items;
    }
}

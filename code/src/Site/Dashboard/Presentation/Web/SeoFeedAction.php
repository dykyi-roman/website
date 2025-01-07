<?php

declare(strict_types=1);

namespace Site\Dashboard\Presentation\Web;

use Orders\DomainModel\Service\OrdersInterface;
use Services\DomainModel\Service\ServicesInterface;
use Site\Dashboard\DomainModel\Dto\FeedItem;
use Site\Dashboard\Presentation\Web\Response\FeedAtomHtmlResponder;
use Site\Dashboard\Presentation\Web\Response\FeedRssHtmlResponder;
use Site\User\DomainModel\Enum\Roles;
use Site\User\DomainModel\Exception\AuthenticationException;
use Site\User\DomainModel\Service\UserFetcher;
use Symfony\Component\Routing\Annotation\Route;

final readonly class SeoFeedAction
{
    public function __construct(
        private OrdersInterface $orders,
        private ServicesInterface $services,
        private UserFetcher $userFetcher,
    ) {
    }

    #[Route('/feed/rss.xml', name: 'feed-rss', defaults: ['_format' => 'xml'])]
    public function rss(FeedRssHtmlResponder $responder): FeedRssHtmlResponder
    {
        return $responder
            ->context([
                'items' => $this->getItems(),
                'website_url' => 'website_url',
            ])->respond();
    }

    #[Route('/feed/atom.xml', name: 'feed-atom', defaults: ['_format' => 'xml'])]
    public function atom(FeedAtomHtmlResponder $responder): FeedAtomHtmlResponder
    {
        return $responder
            ->context([
                'items' => $this->getItems(),
                'website_url' => 'website_url',
            ])
            ->respond();
    }

    /** @return array<int, object> */
    private function getItems(): array
    {
        try {
            $user = $this->userFetcher->fetch();
        } catch (AuthenticationException) {
            return [];
        }

        $arrayItems = [];
        if (in_array(Roles::ROLE_CLIENT->value, $user->getRoles(), true)) {
            $arrayItems = $this->orders->last(10);
        } elseif (in_array(Roles::ROLE_PARTNER->value, $user->getRoles(), true)) {
            $arrayItems = $this->services->last(10);
        }

        if ([] === $arrayItems) {
            $arrayItems = [
                ...$this->orders->last(10),
                ...$this->services->last(10),
            ];
        }

        return array_map(
            static fn (array $item): FeedItem => new FeedItem(
                id: $item['id'],
                title: $item['title'],
                description: $item['description'],
                category: $item['category'],
                url: $item['url'],
                feedbackCount: $item['feedback_count'],
                imageUrl: $item['image_url'],
                features: $item['features'],
                rating: $item['rating'],
                reviewCount: $item['review_count'],
                price: (float) $item['price'],
            ),
            $arrayItems
        );
    }
}

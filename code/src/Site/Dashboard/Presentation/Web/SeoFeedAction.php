<?php

declare(strict_types=1);

namespace Site\Dashboard\Presentation\Web;

use Site\Dashboard\DomainModel\Dto\FeedItem;
use Site\Dashboard\Presentation\Web\Response\FeedAtomHtmlResponder;
use Site\Dashboard\Presentation\Web\Response\FeedRssHtmlResponder;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SeoFeedAction
{
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
        $arrayItems = [];

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

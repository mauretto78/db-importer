<?php
/**
 * This file is part of the DbImporter package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DbImporter\Tests\Entity;

final class Photo
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $albumId;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $thumbnailUrl;

    /**
     * User constructor.
     * @param $id
     * @param $albumId
     * @param $title
     * @param $url
     * @param $thumbnailUrl
     */
    public function __construct(
        $id,
        $albumId,
        $title,
        $url,
        $thumbnailUrl
    ) {
        $this->id = $id;
        $this->albumId = $albumId;
        $this->title = $title;
        $this->url = $url;
        $this->thumbnailUrl = $thumbnailUrl;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getAlbumId()
    {
        return $this->albumId;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getThumbnailUrl()
    {
        return $this->thumbnailUrl;
    }
}

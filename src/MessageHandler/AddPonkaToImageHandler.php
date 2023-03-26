<?php

namespace App\MessageHandler;

use App\Entity\ImagePost;
use App\Message\AddPonkaToImage;
use App\Photo\PhotoFileManager;
use App\Photo\PhotoPonkaficator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AddPonkaToImageHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $ponkaficator;
    private $photoManager;
    private $entityManager;

    public function __construct(PhotoPonkaficator $ponkaficator, PhotoFileManager $photoManager, EntityManagerInterface $entityManager)
    {
        $this->ponkaficator = $ponkaficator;
        $this->photoManager = $photoManager;
        $this->entityManager = $entityManager;
    }

    public function __invoke(AddPonkaToImage $addPonkaToImage)
    {
        $imagePost = $this->entityManager->getRepository(ImagePost::class)->find($addPonkaToImage->getImagePostId());

        if (!$imagePost) {

            if ($this->logger) {
               $this->logger->alert(sprintf('imagepost of id:%d not found, skipping',$addPonkaToImage->getImagePostId()));
            };

            return;
        };

        //throw new \Exception('Im failing constantly!');

        $updatedContents = $this->ponkaficator->ponkafy(
            $this->photoManager->read($imagePost->getFilename())
        );
        $this->photoManager->update($imagePost->getFilename(), $updatedContents);
        $imagePost->markAsPonkaAdded();
        $this->entityManager->flush();
    }
}
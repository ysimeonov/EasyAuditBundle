<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Subscriber;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Annotation\SubscribeDoctrineEvents;
use Xiidea\EasyAuditBundle\Subscriber\DoctrineSubscriber;
use Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie;
use Doctrine\Persistence\ObjectManager;

class DoctrineSubscriberTest extends TestCase
{
    /** @var MockObject */
    private $dispatcher;

    /** @var MockObject */
    private $annotationReader;

    /** @var MockObject */
    private $entityManager;

    /** @var MockObject */
    private $metaData;

    public function setUp(): void    {
        $this->dispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->entityManager = $this->createMock(ObjectManager::class);
        $this->metaData = $this->createMock(ClassMetadata::class);

        $this->entityManager->method('getClassMetadata')
            ->willReturn($this->metaData);

        $this->annotationReader = $this->getMockBuilder('\Doctrine\Common\Annotations\FileCacheReader')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testInstanceOnSubscriber()
    {
        $this->assertInstanceOf('Doctrine\Common\EventSubscriber', new DoctrineSubscriber());
    }

    public function testConstructor()
    {
        $entities = ['entity1', 'entity2'];
        $subscriber = new DoctrineSubscriber($entities);
        $this->assertAttributeEquals($entities, 'entities', $subscriber);
        $subscriber = new DoctrineSubscriber([]);
        $this->assertAttributeEquals([], 'entities', $subscriber);
    }

    public function testSubscribedEvents()
    {
        $subscriber = new DoctrineSubscriber();
        $this->assertEquals([
            'postPersist',
            'postUpdate',
            'preRemove',
            'postRemove',
        ], $subscriber->getSubscribedEvents());
    }

    public function testCreateEventForAnnotatedEntity()
    {
        $annotation = new SubscribeDoctrineEvents(['events' => 'created']);

        $this->initializeAnnotationReader($annotation);

        $subscriber = new DoctrineSubscriber([]);

        $this->invokeCreatedEventCall($subscriber);
    }

    public function testCreateEventForEntityNotConfiguredToTrack()
    {
        $this->initializeAnnotationReader(null);
        $subscriber = new DoctrineSubscriber([]);
        $this->invokeCreatedEventCall($subscriber);
    }

    public function testCreateEventForEntityConfiguredToTrack()
    {
        $this->initializeAnnotationReader();

        $subscriber = new DoctrineSubscriber(['Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie' => ['created']]);

        $this->invokeCreatedEventCall($subscriber);
    }

    public function testCreateEventForEntityConfiguredToTrackAllEvents()
    {
        $this->initializeAnnotationReader();

        $subscriber = new DoctrineSubscriber(['Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie' => []]);

        $this->invokeCreatedEventCall($subscriber);
    }

    public function testUpdateEventForEntityNotConfiguredToTrack()
    {
        $this->initializeAnnotationReader();
        $subscriber = new DoctrineSubscriber([]);
        $this->invokeUpdatedEventCall($subscriber);
    }

    public function testRemovedEventForEntityNotConfiguredToTrack()
    {
        $this->initializeAnnotationReader(null);
        $subscriber = new DoctrineSubscriber([]);
        $this->invokeDeletedEventCall($subscriber);
    }

    public function testRemovedEventForEntityConfiguredToTrackAllEvent()
    {
        $this->initializeAnnotationReader(null);

        $this->mockMetaData();
        $subscriber = new DoctrineSubscriber(['Xiidea\EasyAuditBundle\Tests\Fixtures\ORM\Movie' => []]);
        $this->invokeDeletedEventCall($subscriber);
    }

    private function initializeAnnotationReader($metaData = null)
    {
        $this->annotationReader->expects($this->once())
            ->method('getClassAnnotation')
            ->willReturn($metaData);
    }

    /**
     * @param DoctrineSubscriber $subscriber
     */
    private function invokeCreatedEventCall($subscriber)
    {
        $subscriber->setDispatcher($this->dispatcher);
        $subscriber->setAnnotationReader($this->annotationReader);

        $subscriber->postPersist(new LifecycleEventArgs(new Movie(), $this->entityManager));
    }

    /**
     * @param DoctrineSubscriber $subscriber
     */
    private function invokeUpdatedEventCall($subscriber)
    {
        $subscriber->setDispatcher($this->dispatcher);
        $subscriber->setAnnotationReader($this->annotationReader);

        $subscriber->postUpdate(new LifecycleEventArgs(new Movie(), $this->entityManager));
    }

    /**
     * @param DoctrineSubscriber $subscriber
     */
    private function invokeDeletedEventCall($subscriber)
    {
        $subscriber->setDispatcher($this->dispatcher);
        $subscriber->setAnnotationReader($this->annotationReader);

        $movie = new Movie();
        $subscriber->preRemove(new LifecycleEventArgs($movie, $this->entityManager));
        $subscriber->postRemove(new LifecycleEventArgs($movie, $this->entityManager));
    }

    private function mockMetaData($data = ['id' => 1])
    {
        $this->metaData->method('getIdentifierValues')->willReturn($data);
    }
}

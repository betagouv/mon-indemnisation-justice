<?php

use Acsiomatic\DeviceDetectorBundle\AcsiomaticDeviceDetectorBundle;
use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use League\FlysystemBundle\FlysystemBundle;
use Nelmio\CorsBundle\NelmioCorsBundle;
use Pentatrion\ViteBundle\PentatrionViteBundle;
use Sentry\SentryBundle\SentryBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;

return [
    FrameworkBundle::class => ['all' => true],
    DoctrineBundle::class => ['all' => true],
    DoctrineMigrationsBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    WebProfilerBundle::class => ['dev' => true, 'test' => true],
    TwigExtraBundle::class => ['all' => true],
    SecurityBundle::class => ['all' => true],
    MonologBundle::class => ['all' => true],
    MakerBundle::class => ['dev' => true],
    NelmioCorsBundle::class => ['all' => true],
    ApiPlatformBundle::class => ['all' => true],
    FlysystemBundle::class => ['all' => true],
    DoctrineFixturesBundle::class => ['dev' => true, 'test' => true, 'ci' => true, 'develop' => true],
    SentryBundle::class => ['all' => true],
    DebugBundle::class => ['dev' => true],
    DAMADoctrineTestBundle::class => ['test' => true],
    PentatrionViteBundle::class => ['all' => true],
    AcsiomaticDeviceDetectorBundle::class => ['all' => true],
];

<?php

namespace App\Enums;

enum DockerType: string
{
    case DOCKER = 'docker';
    case DOCKER_COMPOSE = 'docker-compose';
}

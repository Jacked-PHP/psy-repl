<?php

namespace App\Enums;

enum ShellMeta: string
{
    // Docker metadata
    case IS_DOCKER_CONTEXT = 'is_docker_context';
    case DOCKER_CONTAINER = 'docker_container';
    case DOCKER_WORKDIR = 'docker_workdir';

    // Generic metadata
    case SETTINGS_OPEN = 'settings_open';
    case WORD_WRAP = 'word_wrap';
}

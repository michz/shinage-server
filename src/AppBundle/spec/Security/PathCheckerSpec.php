<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace spec\AppBundle\Security;

use AppBundle\Security\PathChecker;
use PhpSpec\ObjectBehavior;

class PathCheckerSpec extends ObjectBehavior
{
    public function it_can_be_constructed()
    {
        $this->shouldBeAnInstanceOf(PathChecker::class);
    }

    public function it_can_forbid_on_empty_paths()
    {
        $this
            ->inAllowedBasePaths(
                '/simple/test/path',
                []
            )
            ->shouldReturn(false);
    }

    public function it_can_detect_correct_path()
    {
        $this
            ->inAllowedBasePaths(
                '/simple/test/path',
                [
                    '/other/unimportant',
                    '/simple/test',
                ]
            )
            ->shouldReturn(true);
    }

    public function it_can_detect_allowed_root_path()
    {
        $this
            ->inAllowedBasePaths(
                '/another/directory/not/in/first',
                [
                    '/simple/test',
                    '/',
                ]
            )
            ->shouldReturn(true);
    }

    public function it_can_detect_correct_path_traversed()
    {
        $this
            ->inAllowedBasePaths(
                '/simple/test/path/../allowed',
                [
                    '/simple/test',
                    '/other/unimportant',
                ]
            )
            ->shouldReturn(true);
    }

    public function it_can_detect_wrong_path_on_same_level_longer()
    {
        $this
            ->inAllowedBasePaths(
                '/simple/test_test',
                [
                    '/other/unimportant',
                    '/simple/test',
                ]
            )
            ->shouldReturn(false);
    }

    public function it_can_detect_wrong_path_on_same_level_shorter()
    {
        $this
            ->inAllowedBasePaths(
                '/simple/test',
                [
                    '/simple/test_test',
                    '/other/unimportant',
                ]
            )
            ->shouldReturn(false);
    }

    public function it_can_detect_wrong_path_traversal()
    {
        $this
            ->inAllowedBasePaths(
                '/simple/test/../../passwd',
                [
                    '/simple',
                    '/other/unimportant',
                ]
            )
            ->shouldReturn(false);
    }

    public function it_can_detect_wrong_path_beyond_root()
    {
        $this
            ->inAllowedBasePaths(
                '/simple/../../../passwd',
                [
                    '/simple',
                    '/simple2',
                ]
            )
            ->shouldReturn(false);
    }

    public function it_can_detect_correct_relative_path()
    {
        $this
            ->inAllowedBasePaths(
                'data/user-1/test/new/../../test.txt',
                [
                    'data/user-1',
                    'data/orga-2/',
                ]
            )
            ->shouldReturn(true);
    }

    public function it_can_detect_correct_relative_root_path()
    {
        $this
            ->inAllowedBasePaths(
                'data',
                [
                    'data',
                ]
            )
            ->shouldReturn(true);
    }

    public function it_can_detect_correct_relative_path_deeper()
    {
        $this
            ->inAllowedBasePaths(
                'data/test',
                [
                    'data/test',
                ]
            )
            ->shouldReturn(true);
    }

    public function it_can_detect_wrong_relative_path_beyond_root()
    {
        $this
            ->inAllowedBasePaths(
                'simple/../../../passwd',
                [
                    'simple',
                    '/',
                ]
            )
            ->shouldReturn(false);
    }
}

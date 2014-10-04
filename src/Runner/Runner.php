<?php
namespace Peridot\Runner;

use Peridot\Core\SpecResult;
use Peridot\Core\Suite;
use Evenement\EventEmitterTrait;

/**
 * Class Runner
 * @package Peridot\Runner
 */
class Runner
{
    use EventEmitterTrait;

    /**
     * @var \Peridot\Core\Suite
     */
    protected $suite;

    /**
     * Constructor
     *
     * @param SpecResult $result
     */
    public function __construct(Suite $suite)
    {
        $this->suite = $suite;
    }

    /**
     * @param SpecResult $result
     */
    public function run(SpecResult $result)
    {
        $oldHandler = set_error_handler(function($errno, $errstr, $errfile, $errline) {
           $this->emit('error', [$errno, $errstr, $errfile, $errline]);
        });

        $result->on('spec:failed', function($spec, $e) {
            $this->emit('fail', [$spec, $e]);
        });

        $result->on('spec:passed', function($spec) {
            $this->emit('pass', [$spec]);
        });

        $result->on('spec:pending', function($spec) {
            $this->emit('pending', [$spec]);
        });

        $this->suite->on('suite:start', function($suite) {
            $this->emit('suite:start', [$suite]);
        });

        $this->suite->on('suite:end', function($suite) {
            $this->emit('suite:end', [$suite]);
        });

        $this->emit('start');
        $this->suite->run($result);
        $this->emit('end');
    }
}

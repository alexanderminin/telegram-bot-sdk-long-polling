<?php

namespace Telegram\Bot\Commands;

/**
 * Interface CommandInterface.
 */
interface CommandInterface
{
    /**
     * Process Inbound Command.
     *
     * @param $telegram
     * @param $action
     * @param $arguments
     * @param $update
     *
     * @return mixed
     */
    public function make($telegram, $action, $arguments, $update);

    /**
     * Process the command.
     *
     * @param $action
     * @param $arguments
     *
     * @return mixed
     */
    public function handle($action, $arguments);
}

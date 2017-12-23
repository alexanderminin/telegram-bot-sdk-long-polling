<?php

namespace Telegram\Bot\Commands;

/**
 * Class HelpCommand.
 */
class HelpCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'help';

    /**
     * @var string Command Description
     */
    protected $description = 'Список доступных команд';

    /**
     * @var array Command Actions Description
     */
    protected $actionDescription = [];

    /**
     * {@inheritdoc}
     */
    public function handle($action, $arguments)
    {
        $commands = $this->telegram->getCommands();

        $text = '';
        foreach ($commands as $commandName => $handler) {
            if ($commandName == 'last_command') continue;
            $text .= sprintf('/%s - %s'.PHP_EOL, $commandName, $handler->getDescription());
            foreach ($handler->getActionsDescription() as $actionName => $description) {
                $text .= sprintf('/%s:%s %s'.PHP_EOL, $commandName, $actionName, $description);
            }
            $text .= PHP_EOL;
        }

        $this->replyWithMessage(compact('text'));
    }
}

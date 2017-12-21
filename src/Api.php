<?php

namespace Telegram\Bot;

use Illuminate\Contracts\Container\Container;
use Telegram\Bot\Commands\CommandBus;
use Telegram\Bot\Commands\CommandInterface;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\HttpClients\GuzzleHttpClient;
use Telegram\Bot\HttpClients\HttpClientInterface;
use Telegram\Bot\Objects\File;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\User;
use Telegram\Bot\Objects\UserProfilePhotos;

/**
 * Class Api.
 */
class Api
{
    /**
     * @var string Version number of the Telegram Bot PHP SDK.
     */
    const VERSION = '1.0';

    /**
     * @var string The name of the environment variable that contains the Telegram Bot API Access Token.
     */
    const BOT_TOKEN_ENV_NAME = 'TELEGRAM_BOT_TOKEN';

    /**
     * @var TelegramClient The Telegram client service.
     */
    protected $client;

    /**
     * @var string Telegram Bot API Access Token.
     */
    protected $accessToken = null;

    /**
     * @var TelegramResponse|null Stores the last request made to Telegram Bot API.
     */
    protected $lastResponse;

    /**
     * @var CommandBus|null Telegram Command Bus.
     */
    protected $commandBus = null;

    /**
     * @var Container IoC Container
     */
    protected static $container = null;

    /**
     * Timeout of the request in seconds.
     *
     * @var int
     */
    protected $timeOut = 60;

    /**
     * Connection timeout of the request in seconds.
     *
     * @var int
     */
    protected $connectTimeOut = 10;

    /**
     * Instantiates a new Telegram super-class object.
     *
     *
     * @param string                     $token               The Telegram Bot API Access Token.
     *                                                        will be asynchronous (non-blocking).
     * @param string|HttpClientInterface $http_client_handler (Optional) Custom HTTP Client Handler.
     *
     * @throws TelegramSDKException
     */
    public function __construct($token = null, $http_client_handler = null)
    {
        $this->accessToken = isset($token) ? $token : getenv(static::BOT_TOKEN_ENV_NAME);
        if (!$this->accessToken) {
            throw new TelegramSDKException('Required "token" not supplied in config and could not find fallback environment variable "'.static::BOT_TOKEN_ENV_NAME.'"');
        }

        $httpClientHandler = null;
        if (isset($http_client_handler)) {
            if ($http_client_handler instanceof HttpClientInterface) {
                $httpClientHandler = $http_client_handler;
            } elseif ($http_client_handler === 'guzzle') {
                $httpClientHandler = new GuzzleHttpClient();
            } else {
                throw new \InvalidArgumentException('The HTTP Client Handler must be set to "guzzle", or be an instance of Telegram\Bot\HttpClients\HttpClientInterface');
            }
        }

        $this->client = new TelegramClient($httpClientHandler);
    }

    /**
     * Returns the TelegramClient service.
     *
     * @return TelegramClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Returns Telegram Bot API Access Token.
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Returns the last response returned from API request.
     *
     * @return TelegramResponse
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Sets the bot access token to use with API requests.
     *
     * @param string $accessToken The bot access token to save.
     *
     * @throws \InvalidArgumentException
     *
     * @return Api
     */
    public function setAccessToken($accessToken)
    {
        if (is_string($accessToken)) {
            $this->accessToken = $accessToken;

            return $this;
        }

        throw new \InvalidArgumentException('The Telegram bot access token must be of type "string"');
    }

    /**
     * Returns SDK's Command Bus.
     *
     * @return CommandBus
     */
    public function getCommandBus()
    {
        if (is_null($this->commandBus)) {
            return $this->commandBus = new CommandBus($this);
        }

        return $this->commandBus;
    }

    /**
     * Add Telegram Command to the Command Bus.
     *
     * @param CommandInterface|string $command
     *
     * @return CommandBus
     */
    public function addCommand($command)
    {
        return $this->getCommandBus()->addCommand($command);
    }

    /**
     * Add Telegram Commands to the Command Bus.
     *
     * @param array $commands
     *
     * @return CommandBus
     */
    public function addCommands(array $commands)
    {
        return $this->getCommandBus()->addCommands($commands);
    }

    /**
     * Remove Telegram Command to the Command Bus.
     *
     * @param string $name
     *
     * @return CommandBus
     */
    public function removeCommand($name)
    {
        return $this->getCommandBus()->removeCommand($name);
    }

    /**
     * Remove Telegram Commands from the Command Bus.
     *
     * @param array $names
     *
     * @return CommandBus
     */
    public function removeCommands(array $names)
    {
        return $this->getCommandBus()->removeCommands($names);
    }

    /**
     * Returns list of available commands.
     *
     * @return Commands\Command[]
     */
    public function getCommands()
    {
        return $this->getCommandBus()->getCommands();
    }

    /**
     * A simple method for testing your bot's auth token.
     * Returns basic information about the bot in form of a User object.
     *
     * @link https://core.telegram.org/bots/api#getme
     *
     * @return User
     */
    public function getMe()
    {
        $response = $this->post('getMe');

        return new User($response->getDecodedBody());
    }

    /**
     * Send text messages.
     *
     * <code>
     * $params = [
     *   'chat_id'                  => '',
     *   'text'                     => '',
     *   'parse_mode'               => '',
     *   'disable_web_page_preview' => '',
     *   'reply_to_message_id'      => '',
     *   'reply_markup'             => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendmessage
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['text']
     * @var string     $params ['parse_mode']
     * @var bool       $params ['disable_web_page_preview']
     * @var int        $params ['reply_to_message_id']
     * @var string     $params ['reply_markup']
     *
     * @return Message
     */
    public function sendMessage(array $params)
    {
        $response = $this->post('sendMessage', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Forward messages of any kind.
     *
     * <code>
     * $params = [
     *   'chat_id'      => '',
     *   'from_chat_id' => '',
     *   'message_id'   => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#forwardmessage
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var int        $params ['from_chat_id']
     * @var int        $params ['message_id']
     *
     * @return Message
     */
    public function forwardMessage(array $params)
    {
        $response = $this->post('forwardMessage', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Broadcast a Chat Action.
     *
     * <code>
     * $params = [
     *   'chat_id' => '',
     *   'action'  => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendchataction
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['action']
     *
     * @throws TelegramSDKException
     *
     * @return TelegramResponse
     */
    public function sendChatAction(array $params)
    {
        $validActions = [
            'typing',
            'upload_photo',
            'record_video',
            'upload_video',
            'record_audio',
            'upload_audio',
            'upload_document',
            'find_location',
        ];

        if (isset($params['action']) && in_array($params['action'], $validActions)) {
            return $this->post('sendChatAction', $params);
        }

        throw new TelegramSDKException('Invalid Action! Accepted value: '.implode(', ', $validActions));
    }

    /**
     * Returns a list of profile pictures for a user.
     *
     * <code>
     * $params = [
     *   'user_id' => '',
     *   'offset'  => '',
     *   'limit'   => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#getuserprofilephotos
     *
     * @param array $params
     *
     * @var int     $params ['user_id']
     * @var int     $params ['offset']
     * @var int     $params ['limit']
     *
     * @return UserProfilePhotos
     */
    public function getUserProfilePhotos(array $params)
    {
        $response = $this->post('getUserProfilePhotos', $params);

        return new UserProfilePhotos($response->getDecodedBody());
    }

    /**
     * Returns basic info about a file and prepare it for downloading.
     *
     * <code>
     * $params = [
     *   'file_id' => '',
     * ];
     * </code>
     *
     * The file can then be downloaded via the link
     * https://api.telegram.org/file/bot<token>/<file_path>,
     * where <file_path> is taken from the response.
     *
     * @link https://core.telegram.org/bots/api#getFile
     *
     * @param array $params
     *
     * @var string  $params ['file_id']
     *
     * @return File
     */
    public function getFile(array $params)
    {
        $response = $this->post('getFile', $params);

        return new File($response->getDecodedBody());
    }

    /**
     * Use this method to receive incoming updates using long polling.
     *
     * <code>
     * $params = [
     *   'offset'  => '',
     *   'limit'   => '',
     *   'timeout' => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#getupdates
     *
     * @param array  $params
     *
     * @var int|null $params ['offset']
     * @var int|null $params ['limit']
     * @var int|null $params ['timeout']
     *
     * @return Update[]
     */
    public function getUpdates(array $params = [])
    {
        $response = $this->post('getUpdates', $params);
        $updates = $response->getDecodedBody();

        $data = [];
        if (isset($updates['result'])) {
            foreach ($updates['result'] as $update) {
                $data[] = new Update($update);
            }
        }

        return $data;
    }

    /**
     * Builds a custom keyboard markup.
     *
     * <code>
     * $params = [
     *   'keyboard'          => '',
     *   'resize_keyboard'   => '',
     *   'one_time_keyboard' => '',
     *   'selective'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#replykeyboardmarkup
     *
     * @param array $params
     *
     * @var array   $params ['keyboard']
     * @var bool    $params ['resize_keyboard']
     * @var bool    $params ['one_time_keyboard']
     * @var bool    $params ['selective']
     *
     * @return string
     */
    public function replyKeyboardMarkup(array $params)
    {
        return json_encode($params);
    }

    /**
     * Hide the current custom keyboard and display the default letter-keyboard.
     *
     * <code>
     * $params = [
     *   'hide_keyboard' => true,
     *   'selective'     => false,
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#replykeyboardhide
     *
     * @param array $params
     *
     * @var bool    $params ['hide_keyboard']
     * @var bool    $params ['selective']
     *
     * @return string
     */
    public static function replyKeyboardHide(array $params = [])
    {
        return json_encode(array_merge(['hide_keyboard' => true, 'selective' => false], $params));
    }

    /**
     * Display a reply interface to the user (act as if the user has selected the bot‘s message and tapped ’Reply').
     *
     * <code>
     * $params = [
     *   'force_reply' => true,
     *   'selective'   => false,
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#forcereply
     *
     * @param array $params
     *
     * @var bool    $params ['force_reply']
     * @var bool    $params ['selective']
     *
     * @return string
     */
    public static function forceReply(array $params = [])
    {
        return json_encode(array_merge(['force_reply' => true, 'selective' => false], $params));
    }

    /**
     * Processes Inbound Commands.
     *
     *
     * @return Update|Update[]
     */
    public function commandsHandler()
    {
        $updates = $this->getUpdates();
        $highestId = -1;

        foreach ($updates as $update) {
            $highestId = $update->getUpdateId();
            $this->processCommand($update);
        }

        //An update is considered confirmed as soon as getUpdates is called with an offset higher than its update_id.
        if ($highestId != -1) {
            $params = [];
            $params['offset'] = $highestId + 1;
            $params['limit'] = 1;
            $this->getUpdates($params);
        }

        return $updates;
    }

    /**
     * Check update object for a command and process.
     *
     * @param Update $update
     */
    protected function processCommand(Update $update)
    {
        $message = $update->getMessage();

        if ($message !== null && $message->has('text')) {
            $this->getCommandBus()->handler($message->getText(), $update);
        }
    }

    /**
     * Determine if a given type is the message.
     *
     * @param string         $type
     * @param Update|Message $object
     *
     * @return bool
     */
    public function isMessageType($type, $object)
    {
        if ($object instanceof Update) {
            $object = $object->getMessage();
        }

        if ($object->has(strtolower($type))) {
            return true;
        }

        return $this->detectMessageType($object) === $type;
    }

    /**
     * Detect Message Type Based on Update or Message Object.
     *
     * @param Update|Message $object
     *
     * @return string|null
     */
    public function detectMessageType($object)
    {
        if ($object instanceof Update) {
            $object = $object->getMessage();
        }

        $types = ['audio', 'document', 'photo', 'sticker', 'video', 'voice', 'contact', 'location', 'text'];

        return $object->keys()
            ->intersect($types)
            ->pop();
    }

    /**
     * Sends a GET request to Telegram Bot API and returns the result.
     *
     * @param string $endpoint
     * @param array  $params
     *
     * @throws TelegramSDKException
     *
     * @return TelegramResponse
     */
    protected function get($endpoint, $params = [])
    {
        return $this->sendRequest(
            'GET',
            $endpoint,
            $params
        );
    }

    /**
     * Sends a POST request to Telegram Bot API and returns the result.
     *
     * @param string $endpoint
     * @param array  $params
     * @param bool   $fileUpload Set true if a file is being uploaded.
     *
     * @return TelegramResponse
     */
    protected function post($endpoint, array $params = [], $fileUpload = false)
    {
        if ($fileUpload) {
            $params = ['multipart' => $params];
        } else {
            $params = ['form_params' => $params];
        }

        return $this->sendRequest(
            'POST',
            $endpoint,
            $params
        );
    }

    /**
     * Sends a request to Telegram Bot API and returns the result.
     *
     * @param string $method
     * @param string $endpoint
     * @param array  $params
     *
     * @throws TelegramSDKException
     *
     * @return TelegramResponse
     */
    protected function sendRequest(
        $method,
        $endpoint,
        array $params = []
    ) {
        $request = $this->request($method, $endpoint, $params);

        return $this->lastResponse = $this->client->sendRequest($request);
    }

    /**
     * Instantiates a new TelegramRequest entity.
     *
     * @param string $method
     * @param string $endpoint
     * @param array  $params
     *
     * @return TelegramRequest
     */
    protected function request(
        $method,
        $endpoint,
        array $params = []
    ) {
        return new TelegramRequest(
            $this->getAccessToken(),
            $method,
            $endpoint,
            $params,
            $this->getTimeOut(),
            $this->getConnectTimeOut()
        );
    }

    /**
     * Magic method to process any "get" requests.
     *
     * @param $method
     * @param $arguments
     *
     * @return bool|TelegramResponse
     */
    public function __call($method, $arguments)
    {
        $action = substr($method, 0, 3);
        if ($action === 'get') {
            /* @noinspection PhpUndefinedFunctionInspection */
            $class_name = studly_case(substr($method, 3));
            $class = 'Telegram\Bot\Objects\\'.$class_name;
            $response = $this->post($method, $arguments[0] ?: []);

            if (class_exists($class)) {
                return new $class($response->getDecodedBody());
            }

            return $response;
        }

        return false;
    }

    /**
     * Set the IoC Container.
     *
     * @param $container Container instance
     *
     * @return void
     */
    public static function setContainer(Container $container)
    {
        self::$container = $container;
    }

    /**
     * Get the IoC Container.
     *
     * @return Container
     */
    public function getContainer()
    {
        return self::$container;
    }

    /**
     * Check if IoC Container has been set.
     *
     * @return boolean
     */
    public function hasContainer()
    {
        return self::$container !== null;
    }

    /**
     * @return int
     */
    public function getTimeOut()
    {
        return $this->timeOut;
    }

    /**
     * @param int $timeOut
     *
     * @return $this
     */
    public function setTimeOut($timeOut)
    {
        $this->timeOut = $timeOut;

        return $this;
    }

    /**
     * @return int
     */
    public function getConnectTimeOut()
    {
        return $this->connectTimeOut;
    }

    /**
     * @param int $connectTimeOut
     *
     * @return $this
     */
    public function setConnectTimeOut($connectTimeOut)
    {
        $this->connectTimeOut = $connectTimeOut;

        return $this;
    }
}

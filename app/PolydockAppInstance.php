<?php

namespace App;

use FreedomtechHosting\PolydockApp\Enums\PolydockAppInstanceStatus;
use FreedomtechHosting\PolydockApp\PolydockAppInstanceInterface;
use FreedomtechHosting\PolydockApp\PolydockAppInterface;
use FreedomtechHosting\PolydockApp\PolydockAppLoggerInterface;
use FreedomtechHosting\PolydockApp\PolydockEngineInterface;

class PolydockAppInstance implements PolydockAppInstanceInterface
{
    /**
     * The status of the app instance
     * @var PolydockAppInstanceStatus
     */
    private PolydockAppInstanceStatus $status;

    private $statusMessage = "";

    /**
     * The key-value store for the app instance
     * @var array
     */
    private array $keyValue = [];

    /**
     * The type of the app instance
     * @var string
     */
    private string $appType;

    /**
     * The engine for the app instance
     * @var PolydockEngineInterface
     */
    private PolydockEngineInterface $engine;

    /**
     * The app for the app instance
     * @var PolydockAppInterface
     */
    private PolydockAppInterface $app;

    /**
     * The logger for the app instance
     * @var PolydockAppLoggerInterface
     */
    private PolydockAppLoggerInterface $logger;

    /**
     * Set the app for the app instance
     * @param PolydockAppInterface $app The app to set
     * @return self Returns the instance for method chaining
     */
    public function setApp(PolydockAppInterface $app) : self
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Get the app for the app instance
     * @return PolydockAppInterface The app
     */
    public function getApp() : PolydockAppInterface
    {
        return $this->app;
    }

    /**
     * Set the type of the app instance
     * @param string $appType The type of the app instance
     * @return self Returns the instance for method chaining
     */
    public function setAppType(string $appType) : self
    {

        if(! class_exists($appType)) {
            throw new PolydockMiniEngineAppNotFoundException($appType);
        }
        
        $appType = str_replace("\\", "_", $appType);

        $this->appType = $appType;

        return $this;

        
    }

    /**
     * Get the type of the app instance
     * @return string The type of the app instance
     */
    public function getAppType() : string
    {
        return $this->appType;
    }

    /**
     * Set the status of the app instance
     * @param PolydockAppInstanceStatus $status The new status to set
     * @param string $statusMessage The status message to set
     * @return self Returns the instance for method chaining
     */
    public function setStatus(PolydockAppInstanceStatus $status, string $statusMessage = "") : self
    {
        $this->status = $status;
        
        if(!empty($statusMessage)) {
            $this->setStatusMessage($statusMessage);
        }

        return $this;
    }

    /**
     * Get the status of the app instance
     * @return PolydockAppInstanceStatus The current status enum value
     */
    public function getStatus() : PolydockAppInstanceStatus
    {
        return $this->status;
    }

    /**
     * Set the status message of the app instance
     * @param string $statusMessage The status message to set
     * @return self Returns the instance for method chaining
     */
    public function setStatusMessage(string $statusMessage) : self
    {
        $this->statusMessage = $statusMessage;
        return $this;
    }

    /**
     * Get the status message of the app instance
     * @return string The status message
     */ 
    public function getStatusMessage() : string 
    {
        return $this->statusMessage;
    }
    
    /**
     * Store a key-value pair for the app instance
     * @param string $key The key to store
     * @param string $value The value to store
     * @return self Returns the instance for method chaining
     */
    public function storeKeyValue(string $key, string $value) : self
    {
        $this->keyValue[$key] = $value;
        return $this;
    }   

    /**
     * Get a stored value by key
     * @param string $key The key to retrieve
     * @return string The stored value, or empty string if not found
     */
    public function getKeyValue(string $key) : string
    {
        return $this->keyValue[$key] ?? "";
    }

    /** 
     * Delete a stored key-value pair
     * @param string $key The key to delete
     * @return self Returns the instance for method chaining
     */
    public function deleteKeyValue(string $key) : self
    {
        unset($this->keyValue[$key]);
        return $this;
    }

    public function setLogger(PolydockAppLoggerInterface $logger) : self
    {
        $this->logger = $logger;
        return $this;
    }

    public function getLogger() : PolydockAppLoggerInterface
    {
        return $this->logger;
    }

    public function info(string $message, array $context = []) : self
    {
        $this->logger->info($message, $context);
        return $this;
    }

    public function error(string $message, array $context = []) : self
    {
        $this->logger->error($message, $context);
        return $this;
    }

    public function warning(string $message, array $context = []) : self
    {
        $this->logger->warning($message, $context);
        return $this;
    }

    public function debug(string $message, array $context = []) : self
    {
        $this->logger->debug($message, $context);
        return $this;
    }

    public function setEngine(PolydockEngineInterface $engine) : self
    {
        $this->engine = $engine;
        return $this;
    }
    
    public function getEngine() : PolydockEngineInterface
    {
        return $this->engine;
    }

    /**
     * Generate a unique project name using an animal, verb and UUID
     * @param string $prefix The prefix for the project name
     * @return string The generated unique name
     */
    public function generateUniqueProjectName(string $prefix) : string 
    {
        $animals = [
            'Lion', 'Tiger', 'Bear', 'Wolf', 'Fox', 'Eagle', 'Hawk', 'Dolphin', 'Whale', 'Elephant',
            'Giraffe', 'Zebra', 'Penguin', 'Kangaroo', 'Koala', 'Panda', 'Gorilla', 'Cheetah', 'Leopard', 'Jaguar',
            'Rhinoceros', 'Hippopotamus', 'Crocodile', 'Alligator', 'Turtle', 'Snake', 'Lizard', 'Iguana', 'Chameleon', 'Gecko',
            'Octopus', 'Squid', 'Jellyfish', 'Starfish', 'Seahorse', 'Shark', 'Stingray', 'Swordfish', 'Tuna', 'Salmon',
            'Owl', 'Parrot', 'Toucan', 'Flamingo', 'Peacock', 'Hummingbird', 'Woodpecker', 'Cardinal', 'Sparrow', 'Robin',
            'Butterfly', 'Dragonfly', 'Ladybug', 'Beetle', 'Ant', 'Spider', 'Scorpion', 'Crab', 'Lobster', 'Shrimp',
            'Deer', 'Moose', 'Elk', 'Bison', 'Buffalo', 'Antelope', 'Gazelle', 'Camel', 'Llama', 'Alpaca',
            'Raccoon', 'Badger', 'Beaver', 'Otter', 'Meerkat', 'Mongoose', 'Weasel', 'Ferret', 'Skunk', 'Armadillo',
            'Sloth', 'Orangutan', 'Chimpanzee', 'Baboon', 'Lemur', 'Gibbon', 'Marmoset', 'Tamarin', 'Capuchin', 'Macaque',
            'Platypus', 'Echidna', 'Opossum', 'Wombat', 'Tasmanian', 'Dingo', 'Quokka', 'Numbat', 'Wallaby', 'Bilby',
            'Hamster', 'Hedgehog', 'Rabbit', 'Mouse', 'Rat', 'Squirrel', 'Chipmunk', 'Mole', 'Vole', 'Gopher',
            'Falcon', 'Vulture', 'Raven', 'Crow', 'Magpie', 'Pigeon', 'Dove', 'Swan', 'Goose', 'Duck',
            'Seal', 'Walrus', 'Penguin', 'Polar Bear', 'Arctic Fox', 'Narwhal', 'Beluga', 'Orca', 'Puffin', 'Albatross',
            'Manta Ray', 'Barracuda', 'Piranha', 'Clownfish', 'Angelfish', 'Lionfish', 'Moray Eel', 'Seahorse', 'Cuttlefish', 'Nautilus',
            'Praying Mantis', 'Grasshopper', 'Cricket', 'Cicada', 'Firefly', 'Moth', 'Wasp', 'Hornet', 'Bee', 'Caterpillar',
            'Pangolin', 'Anteater', 'Aardvark', 'Tapir', 'Okapi', 'Capybara', 'Peccary', 'Coati', 'Binturong', 'Civet',
            'Mandrill', 'Proboscis', 'Langur', 'Howler', 'Spider Monkey', 'Siamang', 'Tarsier', 'Galago', 'Loris', 'Aye-aye',
            'Dugong', 'Manatee', 'Porpoise', 'Dolphin', 'Pilot Whale', 'Sperm Whale', 'Blue Whale', 'Humpback', 'Right Whale', 'Bowhead',
            'Komodo Dragon', 'Monitor Lizard', 'Bearded Dragon', 'Skink', 'Gila Monster', 'Basilisk', 'Tuatara', 'Thorny Devil', 'Frilled Neck', 'Horned Lizard',
            'Red Panda', 'Sun Bear', 'Spectacled Bear', 'Sloth Bear', 'Moon Bear', 'Grizzly', 'Black Bear', 'Brown Bear', 'Kodiak', 'Cave Bear'
        ];
        
        $verbs = [
            'Sleeping', 'Running', 'Jumping', 'Flying', 'Swimming',
            'Dancing', 'Singing', 'Playing', 'Hunting', 'Dreaming',
            'Climbing', 'Diving', 'Soaring', 'Prowling', 'Leaping',
            'Gliding', 'Stalking', 'Bouncing', 'Dashing', 'Floating',
            'Sprinting', 'Hopping', 'Crawling', 'Sliding', 'Swinging',
            'Pouncing', 'Galloping', 'Prancing', 'Skipping', 'Strolling',
            'Wandering', 'Exploring', 'Roaming', 'Meandering', 'Trotting',
            'Charging', 'Lunging', 'Darting', 'Zigzagging', 'Circling',
            'Twirling', 'Spinning', 'Rolling', 'Tumbling', 'Flipping',
            'Stretching', 'Yawning', 'Resting', 'Lounging', 'Relaxing'
        ];

        $colors = [
            'Red', 'Blue', 'Green', 'Yellow', 'Purple', 'Orange', 'Silver', 'Gold'
        ];
        
        $animal = str_replace(' ', '', $animals[array_rand($animals)]);
        $verb = $verbs[array_rand($verbs)];
        $color = $colors[array_rand($colors)];
        return strtolower($prefix . '-' . $verb . '-' . $color . '-' . $animal . '-' . uniqid());
    }
    
}
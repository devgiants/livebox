<?php

namespace Devgiants\Model;

class NatRule {

	const ORIGIN = 'webui';

	const PROTOCOL_TCP = 6;
	const PROTOCOL_UDP = 17;
	const PROTOCOL_BOTH = '6,17';
	const PROTOCOL_BOTH_INTERNAL = 1000;

	/**
	 * @var string
	 */
	private $id;
	/**
	 * @var boolean
	 */
	private $enable = true;
	/**
	 * @var string
	 */
	private $description;
	/**
	 * @var string
	 */
	private $destinationIPAddress;
	/**
	 * @var string
	 */
	private $externalPort;
	/**
	 * @var string
	 */
	private $internalPort;
	/**
	 * @var string
	 */
	private $origin = self::ORIGIN;
	/**
	 * @var boolean
	 */
	private $persistent = true;
	/**
	 * @var int|string
	 */
	private $protocol = self::PROTOCOL_TCP;
	/**
	 * @var string
	 */
	private $sourceInterface = 'data';

    /**
     * @var string
     */
    private $sourcePrefix = '';

	/**
	 * NatRule constructor.
	 */
	public function __construct() {
	}

	/**
	 * @return string
	 */
	public function getId() : string {
		return $this->id;
	}

	/**
	 * @return boolean
	 */
	public function getEnable() : bool {
		return $this->enable;
	}

	/**
	 * @param string $id
	 * @return void
	 */
	public function setId(string $id) {
		$this->id = str_replace(self::ORIGIN.'_', '', $id);
	}
	/**
	 * @param boolean $enabled
	 * @return void
	 */
	public function setEnable(bool $enabled) {
		$this->enable = $enabled;
	}
	/**
	 * @param string $description
	 * @return void
	 */
	public function setDescription(string $description) {
		$this->description = $description;
	}
	/**
	 * @param string $destinationIPAddress
	 * @return void
	 */
	public function setDestinationIPAddress(string $destinationIPAddress) {
		$this->destinationIPAddress = $destinationIPAddress;
	}
	/**
	 * @param integer $externalPort
	 * @return void
	 */
	public function setExternalPort(int $externalPort) {
		$this->externalPort = $externalPort;
	}
	/**
	 * @param integer $internalPort
	 * @return void
	 */
	public function setInternalPort(int $internalPort) {
		$this->internalPort = $internalPort;
	}
	/**
	 * @param string $origin
	 * @return void
	 */
	public function setOrigin(string $origin) {
		$this->origin = $origin;
	}
	/**
	 * @param boolean $persistent
	 * @return void
	 */
	public function setPersistent(bool $persistent) {
		$this->persistent = $persistent;
	}
	/**
	 * Set protocol
	 *
	 * @param integer $protocol
	 * @return void
	 */
	public function setProtocol(int $protocol) {
		if (self::PROTOCOL_BOTH_INTERNAL === $protocol) {
			$protocol = self::PROTOCOL_BOTH;
		}
		$this->protocol = $protocol;
	}
	/**
	 * Set source interface
	 *
	 * @param string $sourceInterface
	 * @return void
	 */
	public function setSourceInterface(string $sourceInterface) {
		$this->sourceInterface = $sourceInterface;
	}

    /**
     * @return string
     */
    public function getSourcePrefix(): ?string
    {
        return $this->sourcePrefix;
    }

    /**
     * @param string $sourcePrefix
     */
    public function setSourcePrefix(?string $sourcePrefix = null): void
    {
        $this->sourcePrefix = $sourcePrefix;
    }



	/**
	 * Get the output for create rule
	 *
	 * @return array
	 */
	public function getOutput() : array {
		$fieldsOutput = [
			'id',
			'enable',
			'description',
			'destinationIPAddress',
			'externalPort',
			'internalPort',
			'origin',
			'persistent',
			'protocol',
			'sourceInterface',
            'sourcePrefix'
		];
		return $this->buildOutput($fieldsOutput);
	}

	/**
	 * Get the output for delete rule
	 *
	 * @return array
	 */
	public function getOutputForDelete() : array {
		$fieldsOutput = [
			'id',
			'destinationIPAddress',
			'origin'
		];
		return $this->buildOutput($fieldsOutput);
	}

	/**
	 * Build array output from needed fields
	 *
	 * @param array $fieldsOutput
	 * @return array
	 */
	protected function buildOutput(array $fieldsOutput) : array {
        $output = [];
        foreach ($fieldsOutput as $field) {
            $method = 'get'.ucfirst($field);
            $output[$field] = method_exists($this, $method) ? $this->$method() : $this->$field;
        }
        return $output;
    }

	/**
	 * Build object from command output
	 *
	 * @param object $input
	 * @return NatRule
	 */
	public static function buildFrom($input) {
		$natRule = new self();
		$natRule->setId($input->Id);
		$natRule->setEnable($input->Enable);
		$natRule->setDescription($input->Description);
		$natRule->setDestinationIPAddress($input->DestinationIPAddress);
		$natRule->setExternalPort((int) $input->ExternalPort);
		$natRule->setInternalPort((int) $input->InternalPort);
		$natRule->setOrigin($input->Origin);
		$natRule->setPersistent((bool) $input->Persistent);
		$natRule->setProtocol((int) $input->Protocol);
		$natRule->setSourceInterface($input->SourceInterface);
		$natRule->setSourcePrefix($input->sourcePrefix);
		return $natRule;
	}

}
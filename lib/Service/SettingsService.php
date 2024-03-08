<?php

declare(strict_types=1);

namespace OCA\Files_Confidential\Service;

use OCA\Files_Confidential\Model\ClassificationLabel;
use OCP\IConfig;
use Psr\Log\LoggerInterface;
use Safe\Exceptions\JsonException;

class SettingsService {
	public function __construct(
		private IConfig $config,
		private LoggerInterface $logger
	) {
	}

	/**
	 * @return list<\OCA\Files_Confidential\Contract\IClassificationLabel>
	 */
	public function getClassificationLabels(): array {
		try {
			/**
			 * @var array $labelsRaw
			 * @psalm-suppress DeprecatedMethod
			 */
			$labelsRaw = \Safe\json_decode($this->config->getAppValue('files_confidential', 'labels', '[]'), true);
		} catch (JsonException $e) {
			$this->logger->warning('Could not load labels setting', ['exception' => $e]);
			return [];
		}
		try {
			return array_values(array_map(fn ($labelRaw) => ClassificationLabel::fromArray($labelRaw), $labelsRaw));
		} catch(\ValueError $e) {
			$this->logger->warning('Could not load labels setting', ['exception' => $e]);
			return [];
		}
	}

	/**
	 * @param array{index:int, name:string, keywords:list<string>, categories:list<string>}[] $labelsRaw
	 * @return void
	 * @throws \Safe\Exceptions\JsonException
	 * @throws \ValueError
	 */
	public function setClassificationLabels(array $labelsRaw): void {
		try {
			$labels = array_map(fn ($labelRaw) => ClassificationLabel::fromArray($labelRaw), $labelsRaw);
		} catch(\ValueError $e) {
			$this->logger->warning('Could not store labels setting', ['exception' => $e]);
			throw $e;
		}
		$array = array_map(fn ($label) => $label->toArray(), $labels);
		try {
			/** @var string $json */
			$json = \Safe\json_encode($array);
		} catch (JsonException $e) {
			$this->logger->warning('Could not store labels setting', ['exception' => $e]);
			throw $e;
		}
		/** @psalm-suppress DeprecatedMethod */
		$this->config->setAppValue('files_confidential', 'labels', $json);
	}
}

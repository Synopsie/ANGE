<?php

/*
 *  ____   __   __  _   _    ___    ____    ____    ___   _____
 * / ___|  \ \ / / | \ | |  / _ \  |  _ \  / ___|  |_ _| | ____|
 * \___ \   \ V /  |  \| | | | | | | |_) | \___ \   | |  |  _|
 *  ___) |   | |   | |\  | | |_| | |  __/   ___) |  | |  | |___
 * |____/    |_|   |_| \_|  \___/  |_|     |____/  |___| |_____|
 *
 * @author Julien
 * @link https://arkaniastudios.com
 * @version 0.0.1-alpha
 *
 */

declare(strict_types=1);

namespace synopsie\pack;

use Symfony\Component\Filesystem\Path;
use synopsie\Engine;
use ZipArchive;
use function is_dir;
use function opendir;
use function readdir;

class ResourcesPackFile {
	private string $resourcePackPath;

	public function __construct(
		string $resourcePackPath
	) {
		$this->resourcePackPath = $resourcePackPath;
	}

	public function getResourcePackPath() : string {
		return $this->resourcePackPath;
	}

	public function savePackInData(string $path, string $addPath = '') : void {
		$dir = opendir(Path::join($path, $addPath));
		if ($dir === false) {
			return;
		}
		$engine = Engine::getInstance();
		while ($file = readdir($dir)) {
			if ($file !== '.' && $file !== '..') {
				if (is_dir(Path::join($path, $addPath, $file))) {
					$this->savePackInData($path, Path::join($addPath, $file));
				} else {
					$engine->saveResource(Path::join($addPath, $file), true);
				}
			}
		}
	}

	public function addToArchive(string $path, string $type, ZipArchive $zip, string $dataPath = '') : void {
		$dir = opendir(Path::join($path, $dataPath));
		if ($dir === false) {
			return;
		}
		while ($file = readdir($dir)) {
			if ($file !== '.' && $file !== '..') {
				if (is_dir(Path::join($path, $dataPath, $file))) {
					$this->addToArchive($path, $type, $zip, Path::join($dataPath, $file));
				} else {
					$zip->addFile(Path::join($path, $dataPath, $file), Path::join($type, $dataPath, $file));
				}
			}
		}
	}

	public function zipPack(string $path, string $zipPath, string $type) : void {
		$zip = new ZipArchive();
		$zip->open(Path::join($zipPath, $type . '.zip'), ZipArchive::CREATE);
		$this->addToArchive($path, $type, $zip);
		$zip->close();
	}

}

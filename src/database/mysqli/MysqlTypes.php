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

namespace synopsie\database\mysqli;

/**
 * @see https://dev.mysql.com/doc/internals/en/com-query-response.html#column-type
 */
interface MysqlTypes {
	public const DECIMAL     = 0x00;
	public const TINY        = 0x01;
	public const SHORT       = 0x02;
	public const LONG        = 0x03;
	public const FLOAT       = 0x04;
	public const DOUBLE      = 0x05;
	public const NULL        = 0x06;
	public const TIMESTAMP   = 0x07;
	public const LONGLONG    = 0x08;
	public const INT24       = 0x09;
	public const DATE        = 0x0A;
	public const TIME        = 0x0B;
	public const DATETIME    = 0x0C;
	public const YEAR        = 0x0D;
	public const NEWDATE     = 0x0E;
	public const VARCHAR     = 0x0F;
	public const BIT         = 0x10;
	public const TIMESTAMP2  = 0x11;
	public const DATETIME2   = 0x12;
	public const TIME2       = 0x13;
	public const NEWDECIMAL  = 0xF6;
	public const ENUM        = 0xF7;
	public const SET         = 0xF8;
	public const TINY_BLOB   = 0xF9;
	public const MEDIUM_BLOB = 0xFA;
	public const LONG_BLOB   = 0xFB;
	public const BLOB        = 0xFC;
	public const VAR_STRING  = 0xFD;
	public const STRING      = 0xFE;
	public const GEOMETRY    = 0xFF;

}

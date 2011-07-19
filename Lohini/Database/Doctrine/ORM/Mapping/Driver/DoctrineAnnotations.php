<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Doctrine\ORM\Mapping;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * I realy do feel bad for definning class in foreign namespace
 * but I have a good reason. This little hack prevents me from doing much uglier things.
 *
 * In order to be able to define own annotation without namespace prefix (ugly) I'm forced
 * to create another AnnotationReader instance and read the damn class fucking twice,
 * to be able to have annotation in my own namespace, without prefix.
 *
 * So fuck it, this is the best god damn fucking way. Don't you dare to question my sanity.
 */

/* Annotations */

class DiscriminatorEntry
extends \Doctrine\Common\Annotations\Annotation
{
	public $name;
}

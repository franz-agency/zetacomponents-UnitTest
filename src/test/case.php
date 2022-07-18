<?php
/**
 * File contaning the abstract ezcTestCase class.
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @package UnitTest
 * @version //autogentag//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
 * Abstract base class for all Zeta Components test cases.
 *
 * @package UnitTest
 * @version //autogentag//
 */
abstract class ezcTestCase extends PHPUnit\Framework\TestCase
{
    /**
     * Do not mess with the temp dir, otherwise the removeTempDirectory might
     * remove the wrong directory.
     */
    private $tempDir;

    /**
     * Creates and returns the temporary directory.
     *
     * @param string $prefix  Set the prefix of the temporary directory.
     *
     * @param string $path    Set the location of the temporary directory. If
     *                        set to false, the temporary directory will
     *                        probably placed in the /tmp directory.
     */
    protected function createTempDir( $prefix, $path = 'run-tests-tmp' )
    {
        if ( !is_dir( $path ) )
        {
            mkdir( $path );
        }
        if ( $tempname = tempnam( $path, $prefix ))
        {
            unlink( $tempname );
            if ( mkdir( $tempname ) )
            {
                $this->tempDir = $tempname;
                return $tempname;
            }
        }

        return false;
    }

    /**
     * Get the name of the temporary directory.
     */
    public function getTempDir()
    {
        return $this->tempDir;
    }

    /**
     * Remove the temp directory.
     */
    public function removeTempDir()
    {
        if ( $this->tempDir && file_exists( $this->tempDir ) )
        {
            $this->removeRecursively( $this->tempDir );
        }
    }

    public function cleanTempDir()
    {
        if ( is_dir( $this->tempDir ) )
        {
            if ( $dh = opendir( $this->tempDir ) )
            {
                while ( ( $file = readdir( $dh ) ) !== false )
                {
                    if ( $file[0] != "." )
                    {
                        $this->removeRecursively( $this->tempDir . DIRECTORY_SEPARATOR . $file );
                    }
                }
            }
        }
    }

    private function removeRecursively( $entry )
    {
        if ( is_file( $entry ) || is_link( $entry ) )
        {
            // Some extra security that you're not erasing your harddisk :-).
            if ( strncmp( $this->tempDir, $entry, strlen( $this->tempDir ) ) == 0 )
            {
                return unlink( $entry );
            }
        }

        if ( is_dir( $entry ) )
        {
            if ( $dh = opendir( $entry ) )
            {
                while ( ( $file = readdir( $dh ) ) !== false )
                {
                    if ( $file != "." && $file != '..' )
                    {
                        $this->removeRecursively( $entry . DIRECTORY_SEPARATOR . $file );
                    }
                }

                closedir( $dh );
                rmdir( $entry );
            }
        }
    }

    /**
     * Checks if $expectedValues are properly set on $propertyName in $object.
     */
    public function assertSetProperty( $object, $propertyName, $expectedValues )
    {
        if ( is_array( $expectedValues ) )
        {
            foreach ( $expectedValues as $value )
            {
                $object->$propertyName = $value;
                $this->assertEquals( $value, $object->$propertyName );
            }
        }
        else
        {
            $this->fail( "Invalid test: expectedValues is not an array." );
        }
    }

    /**
     * Checks if $setValues fail when set on $propertyName in $object.
     * Setting the property must result in an exception.
     */
    public function assertSetPropertyFails( $object, $propertyName, $setValues )
    {
        foreach ( $setValues as $value )
        {
            try
            {
                $object->$propertyName = $value;
            }
            catch ( Exception $e )
            {
                continue;
            }

            $this->fail( "Setting property $propertyName to $value did not fail." );
        }
    }

    /**
     * Compatibility layer for PHPUnit methods that have changed in naming or
     * behavior slightly across versions
     */
    public function __call( $method, $arguments )
    {
        if ( $method === 'getMock' )
        {
            return $this->getMockBuilder( $arguments[0] )->setMethods( $arguments[1] ?? [] )->getMock();
        }

        throw BadMethodCallException( $method . ' does not exist.' );
    }

    /**
     * Implementation of readAttribute that PHPUnit dropped
     */
    public static function readAttribute( $object, $attribute )
    {
        $reflectionObject = new ReflectionClass( $object );
        $reflectionProperty = $reflectionObject->getProperty( $attribute );
        return $reflectionProperty->getValue( $object );
    }

    /**
     * Implementation of assertAttributeSame that PHPUnit dropped
     */
    public static function assertAttributeSame( $expectedValue, $property, $object )
    {
        $actualValue = self::readAttribute( $object, $property );

        return self::assertSame( $actualValue, $expectedValue );
    }
}
?>

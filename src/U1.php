<?php

/**
 * U1
 * 
 * @package U1 PHP
 * @copyright Copyright (C) 2012 Michael Gerhaeuser
 * @author Michael Gerhaeuser
 * @license http://www.opensource.org/licenses/mit-license.html
 */

require_once("Exception.php");
require_once("OAuth.php");

class U1 {

    /**
     * API URI
     *
     * @var string
     */
    protected $api_url = "https://one.ubuntu.com/api/file_storage/v1";

    /**
     * API Content URI
     * This is about to change, see the U1 docs about this issue:
     *   https://one.ubuntu.com/developer/files/store_files/cloud
     *
     * @var string
     */
    protected $api_content_url = "https://files.one.ubuntu.com";

    /**
     * OAuth connection
     * @var U1_OAuth
     */
    protected $oauth;

    /**
     * Constructor
     *
     * @param U1_OAuth $oauth
     */
    public function __construct(U1_OAuth $oauth) {
        $this->oauth = $oauth;
    }

    /**
     * Encode a path for use as a GET parameter.
     *
     * @param string $str
     *
     * @return string
     */
    private function encode($str) {
        return str_replace(" ", "%20", $str);
    }

    /**
     * Retrieve basic information about the user like the user id, quota, name and root volumes.
     *
     * @return array
     */
    public function getAccountInfo() {
        $data = $this->oauth->fetch($this->api_url);
        return json_decode($data['body'], true);
    }

    /**
     * A list of volumes with information about each volume.
     *
     * @return array
     */
    public function getVolumes() {
        $data = $this->oauth->fetch($this->api_url . "/volumes");
        return json_decode($data["body"], true);
    }

    /**
     * Fetches information about a file or a folder.
     *
     * @param string $volume
     * @param string $path
     * @param bool $include_children Fetch information about the contents of the folder, too.
     *
     * @return array
     */
    public function getMetadata($volume, $path, $include_children = true) {
        $args = array();
        if ($include_children) {
            $args["include_children"] = "true";
        }

        $uri = $this->api_url . $this->encode($volume) . $this->encode($path);
        $data = $this->oauth->fetch($uri, $args);

        return json_decode($data["body"], true);
    }

    /**
     * Gather metadata of a volume.
     *
     * @param string $volume Path of the volume, leading slash is required.
     *
     * @return array
     */
    public function getVolumeMetadata($volume) {
        $data = $this->oauth->fetch($this->api_url . "/volumes" . $this->encode($volume));
        return json_decode($data["body"], true);
    }

    /**
     * Create a new volume. Volumes can not be nested.
     *
     * @param string $volume Path of the new volume, leading slash is required.
     *
     * @return array
     */
    public function createVolume($volume) {
        $uri = $this->api_url . '/volumes' . $this->encode($volume);
        $data = $this->oauth->fetch($uri, "", "PUT");

        return json_decode($data["body"], true);
    }

    /**
     * Deletes a volume and all of its contents.
     *
     * @param string $volume Path of the volume, leading slash is required.
     *
     * @return array
     */
    public function deleteVolume($volume) {
        $uri = $this->api_url . '/volumes' . $this->encode($content_path);
        $data = $this->oauth->fetch($uri, "", "DELETE");

        return json_decode($data["body"], true);
    }

    /**
     * Upload or update a file.
     *
     * @param string $content_path The content_path of the file. This is about to change at some point,
     *     please read https://one.ubuntu.com/developer/files/store_files/cloud for additional information.
     *
     * @return array
     */
    public function putFile($content_path, $content) {
        $uri = $this->api_content_url . $this->encode($content_path);
        $data = $this->oauth->fetch($uri, $content, "PUT");

        return json_decode($data["body"], true);
    }

    /**
     * Retrieve the content of a file.
     *
     * @param string $content_path The content_path of the file. This is about to change at some point,
     *     please read https://one.ubuntu.com/developer/files/store_files/cloud for additional information.
     *
     * @return string
     */
    public function getFile($content_path) {
        $uri = $this->api_content_url . $this->encode($content_path);
        $data = $this->oauth->fetch($uri);

        return $data["body"];
    }

    /**
     * Delete a file or a folder. In case a folder is deleted all its content is deleted, too.
     *
     * @param string $volume
     * @param string $path
     *
     * @return array
     */
    public function delete($volume, $path) {
        $uri = $this->api_url . $this->encode($volume) . $this->encode($path);
        $data = $this->oauth->fetch($uri, "", "DELETE");

        return json_decode($data["body"], true);
    }

    /**
     * Creates a new directory inside a given volume.
     *
     * @param string $volume Path of the volume, leading slash is required.
     * @param string $path
     *
     * @return array
     */
    public function createDirectory($volume, $path) {
        $options = array(
            "kind" => "directory"
        );

        $uri = $this->api_url . $this->encode($volume) . $this->encode($path);
        $data = $this->oauth->fetch($uri, json_encode($options), "PUT", array("Content-Type" => "application/json"));

        return json_decode($data["body"], true);
    }

    public function move($volume, $path, $new_path) {
        // todo
    }
    
    public function publish($volume, $path) {
        // todo
    }
}

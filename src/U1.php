<?php

require_once("Exception.php");
require_once("OAuth.php");

class U1 {

    /**
     * API URI
     * @var string
     */
    protected $api_url = "https://one.ubuntu.com/api/file_storage/v1";

    protected $api_content_url = "https://files.one.ubuntu.com";

    /**
     * OAuth connection
     * @var U1_OAuth
     */
    protected $oauth;

    private function encode($str) {
        return str_replace(" ", "%20", $str);
    }

    public function __construct(U1_OAuth $oauth) {
        $this->oauth = $oauth;
    }

    public function getAccountInfo() {
        $data = $this->oauth->fetch($this->api_url);
        return json_decode($data['body'], true);
    }

    public function getVolumes() {
        $data = $this->oauth->fetch($this->api_url . "/volumes");
        return json_decode($data["body"], true);
    }

    public function getMetadata($volume, $path, $include_children = true) {
        $args = array();
        if ($include_children) {
            $args["include_children"] = "true";
        }

        $uri = $this->api_url . $this->encode($volume) . $this->encode($path);
        $data = $this->oauth->fetch($uri, $args);

        return json_decode($data["body"], true);
    }

    public function getVolumeMetadata($volume) {
        // todo
        // GET /api/file_storage/v1/volumes
    }

    public function createVolume($volume) {
        // todo
        // GET /api/file_storage/v1/volumes
    }

    public function deleteVolume($volume) {
        // todo
        // DELETE /api/file_storage/v1/volumes/path/to/volume
    }

    public function putFile($content_path, $content) {
        $uri = $this->api_content_url . $this->encode($content_path);
        $data = $this->oauth->fetch($uri, $content, "PUT");

        return json_decode($data["body"], true);
    }

    public function getFile($content_path) {
        // GET /api/file_storage/v1/ + <file.content_path>
        $uri = $this->api_content_url . $this->encode($content_path);
        $data = $this->oauth->fetch($uri);

        return $data["body"];
    }

    public function deleteFile($volume, $path) {
        // todo
        // DELETE /api/file_storage/v1/~/path/to/volume/path/to/node
    }

    public function createDirectory($volume, $path) {
        $options = array(
            "kind" => "directory"
        );

        $uri = $this->api_url . $this->encode($volume) . $this->encode($path);
        $data = $this->oauth->fetch($uri, json_encode($options), "PUT", array("Content-Type" => "application/json"));

        return json_decode($data["body"], true);
    }

    public function move($volume, $path) {
        // todo
    }

    public function delete($volume, $path) {
        // todo
    }
}

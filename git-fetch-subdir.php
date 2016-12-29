<?php


//------------------------------------------------------------------------------/
// FETCH GIT SUBDIR: https://github.com/lingtalfi/Git-fetch-subdir
//------------------------------------------------------------------------------/


//------------------------------------------------------------------------------/
// CONFIG
//------------------------------------------------------------------------------/
// https://github.com/lingtalfi/bashmanager/tree/master/code
$author = 'lingtalfi';
$repository = "bashmanager";
$relPath = 'code';
$dsDir = __DIR__;


//------------------------------------------------------------------------------/
// SCRIPT
//------------------------------------------------------------------------------/
// https://developer.github.com/v3/#rate-limiting
function getJson($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'My User Agent'); // required by the github api
    $data = curl_exec($ch);
    curl_close($ch);
    return json_decode($data, true);
}

function gitError(array $data)
{
    $msg = "Git error";
    if (array_key_exists('message', $data)) {
        $msg .= ": " . $data['message'];
        if (array_key_exists('documentation_url', $data)) {
            $msg .= " (" . $data['documentation_url'] . ")";
        }
    }
    throw new \Exception($msg);
}

function fetchGitSha($author, $repository, $branch = null)
{
    if (null === $branch) {
        $branch = 'heads/master';
    }
    $url = 'https://api.github.com/repos/' . $author . '/' . $repository . '/git/refs/' . $branch;
    $data = getJson($url);
    if (array_key_exists('object', $data)) {
        return $data['object']['sha'];
    } else {
        gitError($data);
    }
}

/**
 * A treeUrl contains the word /tree/ after the repository name:
 * it's an url that appears when you click on a directory in your github website's repository.
 *
 * relPath: doesn't start with a slash
 */
function fetchGitTree($author, $repository, $relPath, $dstDir)
{
    if (is_dir($dstDir)) {

        $sha = fetchGitSha($author, $repository);
        $url = 'https://api.github.com/repos/' . $author . '/' . $repository . '/git/trees/' . $sha . '?recursive=1';
        $data = getJson($url);
        $tree = $data['tree'];
        foreach ($tree as $info) {
            if ('blob' === $info['type']) {

                $path = $info['path'];

                if (false !== strpos($path, $relPath . "/")) {
                    $url = $info['url'];
                    $infoData = getJson($url);

                    if (array_key_exists('content', $infoData)) {
                        $content = $infoData['content'];
                        $encoding = $infoData['encoding'];
                        if ('base64' === $encoding) {
                            $file = $dstDir . '/' . $path;
                            $dir = dirname($file);
                            if (is_string($dir) && !is_dir($dir)) {
                                mkdir($dir, 0777, true);
                            }
                            $content = base64_decode($content);
                            file_put_contents($file, $content);

                        } else {
                            throw new \Exception("Don't know how to handle encoding $encoding ($url)");
                        }
                    } else {
                        gitError($infoData);
                    }
                }
            }
        }
    } else {
        throw new \Exception("dstDir not found: $dstDir");
    }
}


fetchGitTree($author, $repository, $relPath, $dsDir);




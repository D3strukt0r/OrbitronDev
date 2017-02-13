<?php

namespace App\Cloud;

class CloudExplorer
{
    protected $aCommands = array(
        'open'      => array('target' => false, 'tree' => false, 'init' => false, 'mimes' => false),
        'ls'        => array('target' => true, 'mimes' => false),
        'tree'      => array('target' => true),
        'parents'   => array('target' => true),
        'tmb'       => array('targets' => true),
        'file'      => array('target' => true, 'download' => false),
        'size'      => array('targets' => true),
        'mkdir'     => array('target' => true, 'name' => true),
        'mkfile'    => array('target' => true, 'name' => true, 'mimes' => false),
        'rm'        => array('targets' => true),
        'rename'    => array('target' => true, 'name' => true, 'mimes' => false),
        'duplicate' => array('targets' => true, 'suffix' => false),
        'paste'     => array('dst' => true, 'targets' => true, 'cut' => false, 'mimes' => false),
        'upload'    => array('target' => true, 'FILES' => true, 'mimes' => false, 'html' => false),
        'get'       => array('target' => true),
        'put'       => array('target' => true, 'content' => '', 'mimes' => false),
        'archive'   => array('targets' => true, 'type' => true, 'mimes' => false),
        'extract'   => array('target' => true, 'mimes' => false),
        'search'    => array('q' => true, 'mimes' => false),
        'info'      => array('targets' => true),
        'dim'       => array('target' => true),
        'resize'    => array(
            'target' => true,
            'width'  => true,
            'height' => true,
            'mode'   => false,
            'x'      => false,
            'y'      => false,
            'degree' => false,
        ),
        'netmount'  => array(
            'protocol' => true,
            'host'     => true,
            'path'     => false,
            'port'     => false,
            'user'     => true,
            'pass'     => true,
            'alias'    => false,
            'options'  => false,
        ),
    );

    const ERROR_UNKNOWN = 'errUnknown';
    const ERROR_UNKNOWN_CMD = 'errUnknownCmd';
    const ERROR_CONF = 'errConf';
    const ERROR_CONF_NO_JSON = 'errJSON';
    const ERROR_CONF_NO_VOL = 'errNoVolumes';
    const ERROR_INV_PARAMS = 'errCmdParams';
    const ERROR_OPEN = 'errOpen';
    const ERROR_DIR_NOT_FOUND = 'errFolderNotFound';
    const ERROR_FILE_NOT_FOUND = 'errFileNotFound';     // 'File not found.'
    const ERROR_TRGDIR_NOT_FOUND = 'errTrgFolderNotFound'; // 'Target folder "$1" not found.'
    const ERROR_NOT_DIR = 'errNotFolder';
    const ERROR_NOT_FILE = 'errNotFile';
    const ERROR_PERM_DENIED = 'errPerm';
    const ERROR_LOCKED = 'errLocked';        // '"$1" is locked and can not be renamed, moved or removed.'
    const ERROR_EXISTS = 'errExists';        // 'File named "$1" already exists.'
    const ERROR_INVALID_NAME = 'errInvName';       // 'Invalid file name.'
    const ERROR_MKDIR = 'errMkdir';
    const ERROR_MKFILE = 'errMkfile';
    const ERROR_RENAME = 'errRename';
    const ERROR_COPY = 'errCopy';
    const ERROR_MOVE = 'errMove';
    const ERROR_COPY_FROM = 'errCopyFrom';
    const ERROR_COPY_TO = 'errCopyTo';
    const ERROR_COPY_ITSELF = 'errCopyInItself';
    const ERROR_REPLACE = 'errReplace';          // 'Unable to replace "$1".'
    const ERROR_RM = 'errRm';               // 'Unable to remove "$1".'
    const ERROR_RM_SRC = 'errRmSrc';            // 'Unable remove source file(s)'
    const ERROR_UPLOAD = 'errUpload';           // 'Upload error.'
    const ERROR_UPLOAD_FILE = 'errUploadFile';       // 'Unable to upload "$1".'
    const ERROR_UPLOAD_NO_FILES = 'errUploadNoFiles';    // 'No files found for upload.'
    const ERROR_UPLOAD_TOTAL_SIZE = 'errUploadTotalSize';  // 'Data exceeds the maximum allowed size.'
    const ERROR_UPLOAD_FILE_SIZE = 'errUploadFileSize';   // 'File exceeds maximum allowed size.'
    const ERROR_UPLOAD_FILE_MIME = 'errUploadMime';       // 'File type not allowed.'
    const ERROR_UPLOAD_TRANSFER = 'errUploadTransfer';   // '"$1" transfer error.'
    const ERROR_ACCESS_DENIED = 'errAccess';
    const ERROR_NOT_REPLACE = 'errNotReplace';       // Object "$1" already exists at this location and can not be replaced with object of another type.
    const ERROR_SAVE = 'errSave';
    const ERROR_EXTRACT = 'errExtract';
    const ERROR_ARCHIVE = 'errArchive';
    const ERROR_NOT_ARCHIVE = 'errNoArchive';
    const ERROR_ARCHIVE_TYPE = 'errArcType';
    const ERROR_ARC_SYMLINKS = 'errArcSymlinks';
    const ERROR_ARC_MAXSIZE = 'errArcMaxSize';
    const ERROR_RESIZE = 'errResize';
    const ERROR_UNSUPPORT_TYPE = 'errUsupportType';
    const ERROR_NOT_UTF8_CONTENT = 'errNotUTF8Content';
    const ERROR_NETMOUNT = 'errNetMount';
    const ERROR_NETMOUNT_NO_DRIVER = 'errNetMountNoDriver';
    const ERROR_NETMOUNT_FAILED = 'errNetMountFailed';

    const ERROR_SESSION_EXPIRES = 'errSessionExpires';

    const ERROR_CREATING_TEMP_DIR = 'errCreatingTempDir';
    const ERROR_FTP_DOWNLOAD_FILE = 'errFtpDownloadFile';
    const ERROR_FTP_UPLOAD_FILE = 'errFtpUploadFile';
    const ERROR_FTP_MKDIR = 'errFtpMkdir';
    const ERROR_ARCHIVE_EXEC = 'errArchiveExec';
    const ERROR_EXTRACT_EXEC = 'errExtractExec';
}
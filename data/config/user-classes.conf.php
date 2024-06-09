<?php

namespace Shimmie2;

new UserClass("ghost", "base", [
    Permissions::HELLBANNED => true,
]);

new UserClass("anonymous", "base", [
    Permissions::HELLBANNED => true,
]);

new UserClass("user", "base", [
    Permissions::BIG_SEARCH => true,
    Permissions::BULK_DOWNLOAD => true,
    Permissions::BULK_EDIT_IMAGE_RATING => true,
    Permissions::BULK_EDIT_IMAGE_SOURCE => true,
    Permissions::BULK_EDIT_IMAGE_TAG => true,
    Permissions::BULK_PARENT_CHILD => true,
    Permissions::CHANGE_USER_SETTING => true,
    Permissions::CREATE_IMAGE => true,
    Permissions::DELETE_IMAGE => true,
    Permissions::EDIT_FAVOURITES => true,
    Permissions::EDIT_IMAGE_ARTIST => true,
    Permissions::EDIT_IMAGE_RATING => true,
    Permissions::EDIT_IMAGE_RELATIONSHIPS => true,
    Permissions::EDIT_IMAGE_SOURCE => true,
    Permissions::EDIT_IMAGE_TAG => true,
    Permissions::EDIT_IMAGE_TITLE => true,
    Permissions::MASS_TAG_EDIT => true,
    Permissions::NOTES_CREATE => true,
    Permissions::NOTES_EDIT => true,
    Permissions::PERFORM_BULK_ACTIONS => true,
    Permissions::SET_PRIVATE_IMAGE => true,
]);
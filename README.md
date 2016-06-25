# Adept Wordpress Integration
This plugin will create new post types, and syncronise the data between one's account in Adept LMS and Wordpress marketing site.

Plugin functionality:

## Synchronise data between myaccount.adeptlms.com and the wordpress site
Adept LMS has key content types such as courses, groups, meetings and instructors. These content types will be published in Adept LMS, and must be synchronised automatically with Wordpress using cron.

## List meetings from array of group ids 
The plugin should generate a shortcode where user can enter an array of group ids, and it will fetch the group_meetings from Adept, and show as unordered list, with classes so a designer can easily apply styling for the user interface.
`GET /api/group_meetings?group_ids[]=1&group_ids[]=2`

```
<ul>
  <li>Meeting title</li>
  <li>Date...</li>
  ...
</ul>
```

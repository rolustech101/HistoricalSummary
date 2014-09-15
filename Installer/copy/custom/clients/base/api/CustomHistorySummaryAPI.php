<?php

/**
 * This endpoint is related to the custom/clients/base/views/custom-history-summary/custom-history-summary.js view
 * and is adapted from modules/Activities/Popup_picker.php
 *
 * This will pull and format all of the history items related to the module/id passed in.
 */

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

//for instances using the worklog field
if (SugarAutoLoader::fileExists('custom/include/SugarFields/Fields/Worklog/SugarFieldWorklogHelpers.php'))
{
    require_once('custom/include/SugarFields/Fields/Worklog/SugarFieldWorklogHelpers.php');
}


class CustomHistorySummaryAPI extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            //GET
            'fecthHistorySummary' => array(
                //request type
                'reqType' => 'GET',

                //endpoint path
                'path' => array('?', '?', 'custom-history-summary'),

                //endpoint variables
                'pathVars' => array('module', 'id', ''),

                //method to call
                'method' => 'getHistory',

                //short help string to be displayed in the help documentation
                'shortHelp' => 'Fetches the history of a record.',

                //long help to be displayed in the help documentation
                'longHelp' => '',
            ),
        );
    }

    /**
     * Returns the history.
     *
     * @param $api
     * @param $args
     * @return array|mixed
     */
    public function getHistory($api, $args)
    {
        global $app_strings;
        global $app_list_strings;
        global $timedate;

        $history_list = array();
        $focus = BeanFactory::getBean($args['module'], $args['id']);

        $activitiesRels = array('tasks' => 'Task', 'meetings' => 'Meeting', 'calls' => 'Call', 'emails' => 'Email', 'notes' => 'Note');
        //Setup the arrays to store the linked records.
        foreach($activitiesRels as $relMod => $beanName) {
            $varname = "focus_" . $relMod . "_list";
            $$varname = array();
        }
        foreach($focus->get_linked_fields() as $field => $def) {
            if ($focus->load_relationship($field)) {
                $relTable = $focus->$field->getRelatedTableName();
                if (in_array($relTable, array_keys($activitiesRels)))
                {
                    $varname = "focus_" . $relTable . "_list";
                    $$varname = sugarArrayMerge($$varname, $focus->get_linked_beans($field,$activitiesRels[$relTable],array(),0,100));
                }

            }
        }

        foreach ($focus_tasks_list as $task) {
            $sort_date_time='';
            if (empty($task->date_due) || $task->date_due == '0000-00-00') {
                $date_due = '';
            }
            else {
                $date_due = $task->date_due;
            }

            if ($task->status != "Not Started" && $task->status != "In Progress" && $task->status != "Pending Input") {
                $ts = '';
                if(!empty($task->fetched_row['date_due'])) {
                    //tasks can have an empty date due field
                    $ts = $timedate->fromDb($task->fetched_row['date_due'])->ts;
                }
                $history_list[] = array('name' => $task->name,
                    'id' => $task->id,
                    'type' => "Task",
                    'direction' => '',
                    'module' => "Tasks",
                    'status' => $task->status,
                    'parent_id' => $task->parent_id,
                    'parent_type' => $task->parent_type,
                    'parent_name' => $task->parent_name,
                    'contact_id' => $task->contact_id,
                    'contact_name' => $task->contact_name,
                    'date_modified' => $date_due,
                    'description' => $this->getTaskDetails($task),
                    'date_type' => $app_strings['DATA_TYPE_DUE'],
                    'sort_value' => $ts,
                );
            } else {
                $open_activity_list[] = array('name' => $task->name,
                    'id' => $task->id,
                    'type' => "Task",
                    'direction' => '',
                    'module' => "Tasks",
                    'status' => $task->status,
                    'parent_id' => $task->parent_id,
                    'parent_type' => $task->parent_type,
                    'parent_name' => $task->parent_name,
                    'contact_id' => $task->contact_id,
                    'contact_name' => $task->contact_name,
                    'date_due' => $date_due,
                    'description' => $this->getTaskDetails($task),
                    'date_type' => $app_strings['DATA_TYPE_DUE']
                );
            }
        } // end Tasks

        foreach ($focus_meetings_list as $meeting) {

            if (empty($meeting->contact_id) && empty($meeting->contact_name)) {
                $meeting_contacts = $meeting->get_linked_beans('contacts','Contact',array(),0,100);
                if (!empty($meeting_contacts[0]->id) && !empty($meeting_contacts[0]->name)) {
                    $meeting->contact_id = $meeting_contacts[0]->id;
                    $meeting->contact_name = $meeting_contacts[0]->name;
                }
            }
            if ($meeting->status != "Planned") {
                $history_list[] = array('name' => $meeting->name,
                    'id' => $meeting->id,
                    'type' => "Meeting",
                    'direction' => '',
                    'module' => "Meetings",
                    'status' => $meeting->status,
                    'parent_id' => $meeting->parent_id,
                    'parent_type' => $meeting->parent_type,
                    'parent_name' => $meeting->parent_name,
                    'contact_id' => $meeting->contact_id,
                    'contact_name' => $meeting->contact_name,
                    'date_modified' => $meeting->date_start,
                    'description' => $this->formatDescription($meeting->description),
                    'date_type' => $app_strings['DATA_TYPE_START'],
                    'sort_value' => $timedate->fromDb($meeting->fetched_row['date_start'])->ts,
                );
            } else {
                $open_activity_list[] = array('name' => $meeting->name,
                    'id' => $meeting->id,
                    'type' => "Meeting",
                    'direction' => '',
                    'module' => "Meetings",
                    'status' => $meeting->status,
                    'parent_id' => $meeting->parent_id,
                    'parent_type' => $meeting->parent_type,
                    'parent_name' => $meeting->parent_name,
                    'contact_id' => $meeting->contact_id,
                    'contact_name' => $meeting->contact_name,
                    'date_due' => $meeting->date_start,
                    'description' => $this->formatDescription($meeting->description),
                    'date_type' => $app_strings['DATA_TYPE_START']
                );
            }
        } // end Meetings

        foreach ($focus_calls_list as $call) {

            if (empty($call->contact_id) && empty($call->contact_name)) {
                $call_contacts = $call->get_linked_beans('contacts','Contact',array(),0,100);
                if (!empty($call_contacts[0]->id) && !empty($call_contacts[0]->name)) {
                    $call->contact_id = $call_contacts[0]->id;
                    $call->contact_name = $call_contacts[0]->name;
                }
            }

            if ($call->status != "Planned") {
                $history_list[] = array('name' => $call->name,
                    'id' => $call->id,
                    'type' => "Call",
                    'direction' => $call->direction,
                    'module' => "Calls",
                    'status' => $call->status,
                    'parent_id' => $call->parent_id,
                    'parent_type' => $call->parent_type,
                    'parent_name' => $call->parent_name,
                    'contact_id' => $call->contact_id,
                    'contact_name' => $call->contact_name,
                    'date_modified' => $call->date_start,
                    'description' => $this->formatDescription($call->description),
                    'date_type' => $app_strings['DATA_TYPE_START'],
                    'sort_value' => $timedate->fromDb($call->fetched_row['date_start'])->ts,
                );
            } else {
                $open_activity_list[] = array('name' => $call->name,
                    'id' => $call->id,
                    'direction' => $call->direction,
                    'type' => "Call",
                    'module' => "Calls",
                    'status' => $call->status,
                    'parent_id' => $call->parent_id,
                    'parent_type' => $call->parent_type,
                    'parent_name' => $call->parent_name,
                    'contact_id' => $call->contact_id,
                    'contact_name' => $call->contact_name,
                    'date_due' => $call->date_start,
                    'description' => $this->formatDescription($call->description),
                    'date_type' => $app_strings['DATA_TYPE_START']
                );
            }
        } // end Calls

        foreach ($focus_emails_list as $email) {

            if (empty($email->contact_id) && empty($email->contact_name)) {
                $email_contacts = $email->get_linked_beans('contacts','Contact',array(),0,100);
                if (!empty($email_contacts[0]->id) && !empty($email_contacts[0]->name)) {
                    $email->contact_id = $email_contacts[0]->id;
                    $email->contact_name = $email_contacts[0]->name;
                }
            }
            $ts = '';
            if(!empty($email->fetched_row['date_sent'])) {
                //emails can have an empty date sent field
                $ts = $timedate->fromDb($email->fetched_row['date_sent'])->ts;
            }
            $history_list[] = array('name' => $email->name,
                'id' => $email->id,
                'type' => "Email",
                'direction' => '',
                'module' => "Emails",
                'status' => '',
                'parent_id' => $email->parent_id,
                'parent_type' => $email->parent_type,
                'parent_name' => $email->parent_name,
                'contact_id' => $email->contact_id,
                'contact_name' => $email->contact_name,
                'date_modified' => $email->date_start." ".$email->time_start,
                'description' => $this->getEmailDetails($email),
                'date_type' => $app_strings['DATA_TYPE_SENT'],
                'sort_value' => $ts,
            );
        } //end Emails

        // Bug 46439 'No email archived when clicking on View Summary' (All condition)
        if (method_exists($focus,'get_unlinked_email_query'))
        {
            $queryArray = $focus->get_unlinked_email_query(array('return_as_array'=>'true'));
            $query = $queryArray['select'];
            $query .= $queryArray['from'];
            if (!empty($queryArray['join_tables']))
            {
                foreach ($queryArray['join_tables'] as $join_table)
                {
                    if ($join_table != '')
                    {
                        $query .= ', '.$join_table.' ';
                    }
                }
            }
            $query .= $queryArray['join'];
            $query .= $queryArray['where'];
            $emails = BeanFactory::getBean('Emails');
            $focus_unlinked_emails_list = $emails->process_list_query($query, 0);
            $focus_unlinked_emails_list = $focus_unlinked_emails_list['list'];
            foreach ($focus_unlinked_emails_list as $email)
            {
                $email->retrieve($email->id);
                $history_list[] = array(
                    'name' => $email->name,
                    'id' => $email->id,
                    'type' => "Email",
                    'direction' => '',
                    'module' => "Emails",
                    'status' => '',
                    'parent_id' => $email->parent_id,
                    'parent_type' => $email->parent_type,
                    'parent_name' => $email->parent_name,
                    'contact_id' => $email->contact_id,
                    'contact_name' => $email->contact_name,
                    'date_modified' => $email->date_start." ".$email->time_start,
                    'description' => $this->getEmailDetails($email),
                    'date_type' => $app_strings['DATA_TYPE_SENT'],
                    'sort_value' => strtotime($email->fetched_row['date_sent'].' GMT'),
                );
            }
        } //end Unlinked Emails

        foreach ($focus_notes_list as $note)
        {
            if ($note->ACLAccess('view'))
            {
                $history_list[] = array('name' => $note->name,
                    'id' => $note->id,
                    'type' => "Note",
                    'direction' => '',
                    'module' => "Notes",
                    'status' => '',
                    'parent_id' => $note->parent_id,
                    'parent_type' => $note->parent_type,
                    'parent_name' => $note->parent_name,
                    'contact_id' => $note->contact_id,
                    'contact_name' => $note->contact_name,
                    'date_modified' => $note->date_modified,
                    'description' => $this->formatDescription($note->description),
                    'date_type' => $app_strings['DATA_TYPE_MODIFIED'],
                    'sort_value' => strtotime($note->fetched_row['date_modified'].' GMT'),
                );
                if(!empty($note->filename))
                {
                    $count = count($history_list);
                    $count--;
                    $history_list[$count]['filename'] = $note->filename;
                    $history_list[$count]['fileurl'] = UploadFile::get_url($note->filename,$note->id);
                }
            }

        } // end Notes

        $oddRow = true;

        $result = array();
        if (count($history_list) > 0) $history_list = array_csort($history_list, 'sort_value', SORT_DESC);
        foreach($history_list as $activity)
        {
            $activity_fields = array(
                'ID' => $activity['id'],
                'NAME' => $activity['name'],
                'MODULE' => $activity['module'],
                'CONTACT_NAME' => $activity['contact_name'],
                'CONTACT_ID' => $activity['contact_id'],
                'PARENT_TYPE' => $activity['parent_type'],
                'PARENT_NAME' => $activity['parent_name'],
                'PARENT_ID' => $activity['parent_id'],
                'DATE' => $activity['date_modified'],
                'DESCRIPTION' => $activity['description'],
                'DATE_TYPE' => $activity['date_type']
            );
            if (empty($activity['direction'])) {
                $activity_fields['TYPE'] = $app_list_strings['activity_dom'][$activity['type']];
            }
            else {
                $activity_fields['TYPE'] = $app_list_strings['call_direction_dom'][$activity['direction']].' '.$app_list_strings['activity_dom'][$activity['type']];
            }

            switch ($activity['type']) {
                case 'Call':
                    $activity_fields['STATUS'] = $app_list_strings['call_status_dom'][$activity['status']];
                    break;
                case 'Meeting':
                    $activity_fields['STATUS'] = $app_list_strings['meeting_status_dom'][$activity['status']];
                    break;
                case 'Task':
                    $activity_fields['STATUS'] = $app_list_strings['task_status_dom'][$activity['status']];
                    break;
            }

            if (isset($activity['location'])) $activity_fields['LOCATION'] = $activity['location'];
            if (isset($activity['filename'])) {
                $activity_fields['ATTACHMENT'] = "<a href='index.php?entryPoint=download&id=".$activity['id']."&type=Notes' target='_blank'>".SugarThemeRegistry::current()->getImage("attachment","border='0' align='absmiddle'",null,null,'.gif',$activity['filename'])."</a>";
            }

            if (isset($activity['parent_type'])) $activity_fields['PARENT_MODULE'] = $activity['parent_type'];

            $result[] = $activity;
        }

        if (isset($focus->name))
        {
            $name = $focus->name;
        }
        else if ($focus->full_name)
        {
            $name = $focus->full_name;
        }

        $response = array(
            'module_key' => $focus->module_name,
            'name' => $name,
            'history'=> $result
        );

        return $response;
    }

    /**
     * populate the email details
     * @param $email
     * @return string
     */
    function getEmailDetails($email){
        $details = "";

        if(!empty($email->to_addrs)){
            $details .= "To: ".$email->to_addrs."<br>";
        }
        if(!empty($email->from_addr)){
            $details .= "From: ".$email->from_addr."<br>";
        }
        if(!empty($email->cc_addrs)){
            $details .= "CC: ".$email->cc_addrs."<br>";
        }
        if(!empty($email->from_addr) || !empty($email->cc_addrs) || !empty($email->to_addrs)){
            $details .= "<br>";
        }

        // cn: bug 8433 - history does not distinguish b/t text/html emails
        $details .= empty($email->description_html)
            ? $this->formatDescription($email->description)
            : $this->formatDescription(strip_tags(br2nl(from_html($email->description_html))));

        return $details;
    }

    /**
     * populate the task details
     * @param $task
     * @return string
     */
    private function getTaskDetails($task){
        global $app_strings;

        $details = "";
        if (!empty($task->date_start) && $task->date_start != '0000-00-00') {
            $details .= $app_strings['DATA_TYPE_START'].$task->date_start."<br>";
            $details .= "<br>";
        }
        $details .= $this->formatDescription($task->description);

        return $details;
    }

    /**
     * Format the activity description
     * @param $description
     * @return string
     */
    private function formatDescription($description){

        //handle worklog fields
        if (SugarAutoLoader::fileExists('custom/include/SugarFields/Fields/Worklog/SugarFieldWorklogHelpers.php') && SugarFieldWorklogHelpers::isJson($description))
        {
            $description = SugarFieldWorklogHelpers::decodeJsonValue($description);
        }

        $description = htmlspecialchars($description, ENT_QUOTES);
        $description = nl2br($description);

        return $description;
    }
}

?>
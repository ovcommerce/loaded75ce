<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\models\EP;

class Cron
{
    static public function init()
    {
        global $languages_id;
        $languages_id = \common\classes\language::defaultId();
    }

    static public function runExport()
    {
        self::init();

        // find cronned export jobs
        $get_job_r = tep_db_query(
            "SELECT ej.job_id ".
            "FROM ".TABLE_EP_JOB." ej ".
            "  INNER JOIN ".TABLE_EP_DIRECTORIES." ed ON ed.directory_id = ej.directory_id ".
            "WHERE ed.cron_enabled=1 AND ed.directory_type='export' ".
            " AND ej.run_frequency>=0 "
        );
        if ( tep_db_num_rows($get_job_r)>0 ) {
            while($get_job = tep_db_fetch_array($get_job_r)){
                $job_id = $get_job['job_id'];
                $job = Job::loadById($job_id);
                $fileInfo = $job->getFileInfo();
                
                $now = strtotime('now');  
                
                $run_job_now = false;
                if ( $job->run_frequency==0 ) {

                    $need_run_mktime = strtotime($job->run_time);
                    $allow_frame_sec = 5*60; 

                    if ( !empty($job->last_cron_run) ) {
                        $runned_today = date('Ymd',strtotime($job->last_cron_run))==date('Ymd',$now);
                    }else{
                        $runned_today = false;
                    }

                    $exact_time = date('dHi',$need_run_mktime)==date('dHi',$now);
                    $missed_run = ($now>$need_run_mktime) && ($now<($need_run_mktime+$allow_frame_sec));

                    $run_job_now = !$runned_today && ($exact_time || $missed_run);
                }else{
                    $run_job_now = empty($job->last_cron_run) || strtotime('- '.$job->run_frequency.' minutes')>=strtotime($job->last_cron_run);
                }
                if ( $run_job_now ) {
                    tep_db_query("UPDATE ".TABLE_EP_JOB." SET last_cron_run='".date('Y-m-d H:i:s',$now)."' WHERE job_id='".$job->job_id."'");
                    
                    $messages = new \backend\models\EP\Messages([
                        'job_id' => $job_id,
                        'output' => 'console',
                    ]);

                    try{
                        echo "#{$job->job_id} {$job->file_name}\n";
                        $job->run($messages);
                    }catch(\Exception $ex){
                        $messages->info($ex->getMessage()); 
                    }
                }
                
            }
        }
    }

    static public function runImport()
    {
        self::init();

        $autoImportRoot = Directory::loadById(4);
        $autoImportRoot->process(true);

        // find cronned import jobs
        $get_job_r = tep_db_query(
            "SELECT ej.job_id ".
            "FROM ".TABLE_EP_JOB." ej ".
            "  INNER JOIN ".TABLE_EP_DIRECTORIES." ed ON ed.directory_id = ej.directory_id ".
            "WHERE ed.cron_enabled=1 AND ed.directory_type IN('import','import_zip') AND ej.job_state='configured' ".
            " AND ej.run_frequency>=0 "
        );
        if ( tep_db_num_rows($get_job_r)>0 ) {
            while($get_job = tep_db_fetch_array($get_job_r)){
                $job_id = $get_job['job_id'];
                $job = Job::loadById($job_id);

                if ( $job->run_frequency==0 || $job->run_frequency==1 ) {
                    // once run
                    // if ( $job->last_cron_run ) continue;
                }

                $now = strtotime('now');  

                $run_job_now = false;
                if ( $job->run_frequency==0 ) {

                    $need_run_mktime = strtotime($job->run_time);
                    $allow_frame_sec = 5*60; 

                    if ( !empty($job->last_cron_run) ) {
                        $runned_today = date('Ymd',strtotime($job->last_cron_run))==date('Ymd',$now);
                    }else{
                        $runned_today = false;
                    }

                    $exact_time = date('dHi',$need_run_mktime)==date('dHi',$now);
                    $missed_run = ($now>$need_run_mktime) && ($now<($need_run_mktime+$allow_frame_sec));

                    $run_job_now = !$runned_today && ($exact_time || $missed_run);
                }else{
                    $run_job_now = empty($job->last_cron_run) || strtotime('- '.$job->run_frequency.' minutes')>=strtotime($job->last_cron_run);
                }

                if ( $run_job_now ) {
                    tep_db_query("UPDATE ".TABLE_EP_JOB." SET last_cron_run='".date('Y-m-d H:i:s',$now)."' WHERE job_id='".$job->job_id."'");

                    $messages = new \backend\models\EP\Messages([
                        'job_id' => $job_id,
                        'output' => 'console',
                    ]);
                    try{
                        echo "#{$job->job_id} {$job->file_name}\n";
                        $messages->info('Cron start run at '.\common\helpers\Date::formatDateTime(date('Y-m-d H:i:s')));
                        $job->run($messages);
                        tep_db_query(
                            "UPDATE ".TABLE_EP_JOB." ".
                            "SET job_state='processed', last_cron_run='".date('Y-m-d H:i:s',$now)."' ".
                            "WHERE job_id='".$job->job_id."'"
                        );
                        $job->moveToProcessed();
                    }catch(\Exception $ex){
                        $messages->info($ex->getMessage());
                    }
                    $messages->info('Cron finished at '.\common\helpers\Date::formatDateTime(date('Y-m-d H:i:s')));
                }
                
            }
        }
    }
    
    static public function runDatasource($runNow = false)
    {
        self::init();

        $autoImportRoot = Directory::loadById(5);
        $autoImportRoot->process(true);

        // find cronned import jobs
        $get_job_r = tep_db_query(
            "SELECT ej.job_id ".
            "FROM ".TABLE_EP_JOB." ej ".
            "  INNER JOIN ".TABLE_EP_DIRECTORIES." ed ON ed.directory_id = ej.directory_id ".
            "WHERE ed.cron_enabled=1 AND ed.directory_type='datasource' AND ej.job_state='configured' ".
            " AND ej.run_frequency>=0 "
        );
        if ( tep_db_num_rows($get_job_r)>0 ) {
            while($get_job = tep_db_fetch_array($get_job_r)){
                $job_id = $get_job['job_id'];
                $job = Job::loadById($job_id);

                if ( $job->run_frequency==0 || $job->run_frequency==1 ) {
                    // once run
                    // if ( $job->last_cron_run ) continue;
                }

                $now = strtotime('now');

                $run_job_now = false;
                if ( $runNow ) {
                    $run_job_now = true;
                }else
                if ( $job->run_frequency==0 ) {

                    $need_run_mktime = strtotime($job->run_time);
                    $allow_frame_sec = 5*60;

                    if ( !empty($job->last_cron_run) ) {
                        $runned_today = date('Ymd',strtotime($job->last_cron_run))==date('Ymd',$now);
                    }else{
                        $runned_today = false;
                    }

                    $exact_time = date('dHi',$need_run_mktime)==date('dHi',$now);
                    $missed_run = ($now>$need_run_mktime) && ($now<($need_run_mktime+$allow_frame_sec));

                    $run_job_now = !$runned_today && ($exact_time || $missed_run);
                }else{
                    $run_job_now = empty($job->last_cron_run) || strtotime('- '.$job->run_frequency.' minutes')>=strtotime($job->last_cron_run);
                }

                if ( $run_job_now ) {
                    tep_db_query("UPDATE ".TABLE_EP_JOB." SET last_cron_run='".date('Y-m-d H:i:s',$now)."' WHERE job_id='".$job->job_id."'");

                    $messages = new \backend\models\EP\Messages([
                        'job_id' => $job_id,
                        'output' => 'console',
                    ]);
                    try{
                        echo "#{$job->job_id} {$job->file_name}\n";
                        $messages->info('Cron start run at '.\common\helpers\Date::formatDateTime(date('Y-m-d H:i:s')));
                        $job->run($messages);
                        $job->job_state = 'processed';
                        $job->last_cron_run = date('Y-m-d H:i:s',$now);
                        tep_db_query(
                            "UPDATE ".TABLE_EP_JOB." ".
                            "SET job_state='processed', last_cron_run='".$job->last_cron_run."' ".
                            "WHERE job_id='".$job->job_id."'"
                        );
                        $job->moveToProcessed();
                    }catch(\Exception $ex){
                        $messages->info($ex->getMessage());
                        echo $ex->getFile().':'.$ex->getLine()."\n";
                    }
                    $messages->info('Cron finished at '.\common\helpers\Date::formatDateTime(date('Y-m-d H:i:s')));
                }
            }
        }
    }
    
}

<?php
$panl_class = 'panel-default';
$message = '';

if(isset($job_created)) {
    if($job_created == true) {
        $panl_class = 'panel-green';
        $message = $msg;
    } else {
        $panl_class = 'panel-red';
        $message = $msg;
    }
}
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"></h1>
    </div>
    <!-- /.col-lg-12 -->
</div>

<!-- /.row -->
<div class="row">
    <div class="col-lg-8">
        <div class="panel <?php echo $panl_class ?>">
            <div class="panel-heading">
                Create ga job :
                <span><?php echo $message ?></span>
            </div>
            <div class="panel-body">
                <div class="row">
                    
                </div>

                <div class="row">
                    <div class="col-lg-9">
                        <form role="form" method="post" action="/GaTool/task/create">
                            
                            <div class="form-group">
                                <label>Profile :</label>
                                <p class="help-block">choose the profile you wanna retrieve</p>
                                <select name="profile" class="form-control">
                                    <?php foreach ($profiles as $key => $profile) :?>
                                        <option value="<?php echo $key ?>"> <?php echo $profile ?></option>
                                    <?php endforeach;?>
                                </select>
                            </div>
                        
                            <div class="form-group">
                                <label>Table name</label>
                                <input class="form-control" name="table_name" value="<?= isset($template) ? $template->table_name : '' ?>">
                                <p class="help-block">name the table you want to store analytics in</p>
                            </div>
                            <div class="form-group">
                                <label>Metrics :</label>
                                <input class="form-control" name="metrcis" placeholder="" value="<?= isset($template) ? $template->metrcis : '' ?>">
                                <p class="help-block">Add what are the metrecies you want to retrieve from google analytics</p>
                            </div>
                            <div class="form-group">
                                <label>Example :</label>
                                <p class="form-control-static">ga:users,ga:sessions,ga:pageviews</p>
                            </div>
                            
                            <div class="form-group">
                                <label>Dimensions :</label>
                                <input class="form-control" name="dimensions" placeholder="" value="<?= isset($template) ? $template->dimensions : '' ?>">
                                <p class="help-block">Add what are the dimentions you want to retrieve from google analytics</p>
                            </div>
                            <div class="form-group">
                                <label>Example :</label>
                                <p class="form-control-static">ga:day,ga:source,ga:hostname,ga:deviceCategory</p>
                            </div>

                            <div class="form-group">
                                <label>Filters :</label>
                                <input class="form-control" name="filters" placeholder="" value="<?= isset($template) ? $template->filters : '' ?>">
                                <p class="help-block">Add what are the metrecies you want to retrieve from google analytics</p>
                            </div>
                            <div class="form-group">
                                <label>Example :</label>
                                <p class="form-control-static">ga:hostname=~.*google.*</p>
                            </div>

                            <div class="form-group">
                                <label>Segments :</label>
                                <textarea class="form-control" name="segments" placeholder="" >
                                 <?= isset($template) ? $template->segments : '' ?>
                                 </textarea>
                                <p class="help-block">Add what are the segments you want to retrieve from google analytics</p>
                            </div>

                            <div class="form-group">
                                <label>Example :</label>
                                <p class="form-control-static" style="word-break: break-word;">
                                    organic_search::ga:medium=~organic,ga:source=~search|google.com;ga:source!~plus.url.google.com
                                </p>
                            </div>

                            <div class="form-group">
                                <label>From</label>
                                <input class="form-control" name="from" placeholder="<?php echo date('Y-m-d') ?>" value="<?= isset($template) ? $template->date_from : '' ?>">
                            </div>

                            <div class="form-group">
                                <label>To</label>
                                <input class="form-control" name="to" placeholder="<?php echo date('Y-m-d') ?>" value="<?= isset($template) ? $template->date_to : '' ?>">
                            </div>

                            <div class="form-group">
                                <label></label>
                                <input class="form-control" name="template_name" placeholder="template name">
                            </div>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="save_as_template" value="true">save as template
                                </label>
                            </div>
                            
                            </br>
                            <div class="form-group">
                                <label>Output To :</label>
                                <p class="help-block">Choose where to output your data, honstly we prefere Couchbase =D</p>
                                <select name="output_to" class="form-control">
                                    <option value="mysql">mysql</option>
                                    <option value="couchbase">couchbase</option>
                                </select>
                            </div>
                            </br>

                            <button type="submit" class="btn btn-default">Create</button>

                        </form>
                    </div>
                </div>
                <!-- /.row (nested) -->
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-12 -->
    <div class="col-lg-4">
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading ">
                    Recent jobs :
                </div>
                <div class="panel-body">
                    <?php foreach ($job_templates as $template): ?>
                        <a href="<?php echo Yii::app()->createUrl('/GaTool/task/jobtemplate', array('id' => $template->id)) ?>"> <?php echo $template->name ?></a>
                        </br>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading ">
                    job status:
                </div>
                <div class="panel-body">
                    <ul class="dropdown-tasks">
                        <?php foreach ($tasks_status as $key => $task): ?>
                            <li>
                                <div>
                                    <p>
                                        <strong><?php echo $key ?></strong>
                                    </p>
                                    <p><?php echo $task ?></p>
                                    <div class="progress progress-striped?> active">
                                        <?php if($task == 'COMPLETE'): ?>
                                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                                            </div>
                                        <?php endif; ?>
                                        <?php if($task == 'FAILED') :?>
                                            <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                                            </div>
                                        <?php endif;?>
                                        <?php if($task == 'WAITING' || $task == 'RUNNING'): ?>
                                            <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                                            </div>
                                        <?php endif;?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.row -->

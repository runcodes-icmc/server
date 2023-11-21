/**
 * Created by Fabio on 17/01/2015.
 */
var runcodesApp = angular.module('runcodesApp', ['angular-rickshaw','angular-flot']);

runcodesApp.constant("moment", moment);

runcodesApp.controller('ServerTimeController', ['$scope','$sce','$interval','ServerTimeService', function ($scope,$sce,$interval,ServerTimeService) {
    $scope.serverTime = {};
    $scope.serverTime.datetime = {};
    $scope.serverTime.date = "--";
    $scope.serverTime.time = "--";
    $scope.serverTime.datetime.seconds = 100;
    $scope.lastRequest = 301;
    $scope.runningRequest = 0;
    $scope.updateServerTime = $interval(function () {
        if ((parseInt($scope.serverTime.datetime.minutes) >= 59 && parseInt($scope.serverTime.datetime.seconds) >= 59) || ($scope.lastRequest > 300 && $scope.runningRequest == 0)) {
            $scope.runningRequest++;
            ServerTimeService.loadServerTime().then(function (response) {
                $scope.serverTime.datetime = response;
                $scope.lastRequest = 0;
                $scope.runningRequest = 0;
            });
        } else {
            updateServerTimeClient();
        }
        publishServerTime();
    },1000);

    $scope.initializeServerTime = function (date,time) {
        $scope.serverTime.date = date;
        $scope.serverTime.time = time;
    };

    var publishServerTime = function () {
        if (!isNaN($scope.serverTime.datetime.mday)) {
            $scope.serverTime.date = $scope.serverTime.datetime.mday + "/" + $scope.serverTime.datetime.mon + "/" + $scope.serverTime.datetime.year;
            $scope.serverTime.time = $scope.serverTime.datetime.hours + ":" + $scope.serverTime.datetime.minutes + ":" + $scope.serverTime.datetime.seconds;
        }
    };
    var updateServerTimeClient = function () {
        $scope.serverTime.datetime.seconds = parseInt($scope.serverTime.datetime.seconds) + 1;
        if (parseInt($scope.serverTime.datetime.seconds) > 59) {
            $scope.serverTime.datetime.seconds = 0;
            $scope.serverTime.datetime.minutes = parseInt($scope.serverTime.datetime.minutes) + 1;
            if ($scope.serverTime.datetime.minutes < 10) {
                $scope.serverTime.datetime.minutes = '0' + $scope.serverTime.datetime.minutes;
            } else {
                $scope.serverTime.datetime.minutes = '' + $scope.serverTime.datetime.minutes;
            }
        }
        if ($scope.serverTime.datetime.seconds < 10) {
            $scope.serverTime.datetime.seconds = '0' + $scope.serverTime.datetime.seconds;
        } else {
            $scope.serverTime.datetime.seconds = '' + $scope.serverTime.datetime.seconds;
        }
        $scope.lastRequest++;
    };
}]);

runcodesApp.service('ServerTimeService',['$http', function ($http) {
    this.loadServerTime = function () {
        var request = $http.post("/getServerDateTimeJson");
        return (request.then(handleSuccess));
        function handleSuccess(response) {
            return(response.data);
        }
    };
}]);

runcodesApp.controller('OfferingStatsController', ['$scope','$sce','$interval','StatsService', function ($scope,$sce,$interval,StatsService) {
    $scope.offeringInfographic = {};
    $scope.offeringInfographic.students = 0;
    $scope.offeringInfographic.gradespect = 0;
    $scope.offeringInfographic.gradesavg = 0;

    $scope.student = -1;
    $scope.dataset = [{ data: [], yaxis: 1, label: "" }];
    $scope.options = {
        legend: {
            container: "#legend",
            show: true
        },
        bars: {
            show: true,
            barWidth: 0.6,
            align: "center"
        },
        colors: ["#FFFFFF"],
        xaxis: {
            min: 0
        },
        yaxis: {
            min: 0,
            max: 10,
            ticks: 10
        }
    };

    $scope.loadOfferingStats = function(offeringId) {
        StatsService.loadOfferingChartData(offeringId,$scope.student).then(function (chart) {
            $scope.dataset = [{ data: [], yaxis: 1, label: "" }];
            $scope.options.xaxis.ticks = chart.labelsX;
            $scope.options.xaxis.max = chart.labelsX.length + 1;
            $scope.options.colors = chart.colors;

            $scope.dataset[0].data = chart.series[0].data;
            $scope.dataset[0].label = chart.series[0].name;
            if (chart.series[1] !== undefined) {
                $scope.dataset[1] = {};
                $scope.dataset[1].data = chart.series[1].data;
                $scope.dataset[1].label = chart.series[1].name;
                $scope.dataset[1].lines = {show: true};
                $scope.dataset[1].bars = {show: false};
            }
        });
    };

    $scope.loadOfferingInfographicData = function (offeringId) {
        StatsService.loadOfferingInfographicData(offeringId).then(function (data) {
            $scope.offeringInfographic = data;
        });
    };
}]);

runcodesApp.service('StatsService', ['$http',function ($http) {
    this.loadCommitsChartData = function () {
        var request = $http.post("/Stats/commitsChart");
        return (request.then(handleSuccess));
    };

    this.loadVisitsChartData = function () {
        var request = $http.post("/Stats/visitsChart");
        return (request.then(handleSuccess));
    };

    this.loadDiskChartData = function () {
        var request = $http.post("/Stats/diskChart");
        return (request.then(handleSuccess));
    };

    this.loadCommitsInfographicData = function () {
        var request = $http.post("/Stats/commitsInfographic");
        return (request.then(handleSuccess));
    };

    this.loadHomeInfographicData = function () {
        var request = $http.post("/Stats/homeInfographic");
        return (request.then(handleSuccess));
    };

    this.loadHomeServersData = function () {
        var request = $http.post("/Stats/homeServers");
        return (request.then(handleSuccess));
    };

    this.loadOfferingChartData = function (offering_id,participant) {
        var request = $http.post("/Offerings/stats/"+offering_id+"/"+participant);
        return (request.then(handleSuccess));
    };

    this.loadOfferingInfographicData = function (offering_id) {
        var request = $http.post("/Offerings/infographic/"+offering_id);
        return (request.then(handleSuccess));
    };

    function handleSuccess(response) {
        return(response.data);
    }
}]);

runcodesApp.controller('CommitController', ['$scope','$sce','$interval','StatsService', function ($scope,$sce,$interval,StatsService) {
    $scope.filename = "";
    $scope.showFileInfo = false;
    $scope.progress = 0;
    $scope.data = null;
    $scope.submitted = false;

    $scope.submitFile = function () {
        $scope.submitted = true;
        $scope.submitButton = "Uploading...";
        $scope.data.submit();
    }

    $scope.loadFileUpload = function (exercise) {
        $('#userFileUpload').fileupload({
            dataType: 'json',
            url: '/Exercises/commit/' + exercise,
            autoUpload: false,
            dropZone: 'body',
            add: function (e, data) {
                $scope.alertMessage = false;
                $scope.showFileInfo = true;
                $scope.filename = data.files[0].name + " - " + data.files[0].size + " bytes";
                $scope.data = data;
            },
            start: function (e, data) {

            },
            fail: function (e, data) {
                if (data.jqXHR.status == 413) {
                    $scope.alertMessage = "Tamanho do arquivo maior que o máximo permitido";
                    $scope.submitted = false;
                    $scope.data = null;
                    $scope.progress = 0;
                    $scope.filename = "";
                    $scope.showFileInfo = false;
                    $scope.submitButton = $scope.submitButtonDefault;
                } else {
                    if (data.textstatus != "parsererror") {
                        alert("Ocorreu uma falha e seu arquivo não foi enviado, por favor tente novamente");
                    }
                    $scope.submitButton = $scope.submitButtonDefault;
                    location.reload(true);
                }
            },
            progressall: function (e, data) {
                var prog = parseInt(data.loaded / data.total * 100, 10);
                $scope.progress = prog;
            },
            done: function (e, data) {
                if(data.result.files[0].error){
                    $scope.alertMessage = data.result.files[0].error;
                    $scope.filename = "";
                    $scope.showFileInfo = false;
                    $scope.progress = 0;
                    $scope.data = null;
                    $scope.submitted = false;
                    $scope.submitButton = $scope.submitButtonDefault;
                }else{
                    $scope.submitButton = $scope.submitButtonReloading;
                    location.reload(true);
                }
            }
        });
    };
}]);

runcodesApp.service('CommitsExerciseCasesService', ['$http',function ($http) {
    this.loadCommitExerciseCase = function (id) {
        var request = $http.post("/CommitsExerciseCases/viewCase/" + id);
        return (request.then(handleSuccess));
//                .success(function(response) {
//                    scope.commitsExerciseCaseView = response;
//                    alert(response);
//                    return true;
//                })
//                .error(function () {
//                    return false;
//                });
        function handleSuccess(response) {
            return(response.data);
        }
    };
}]);

runcodesApp.controller('ExerciseCasesController', ['$scope','$sce','CommitsExerciseCasesService', function ($scope,$sce,CommitsExerciseCasesService) {
    $scope.caseId = -1;
    $scope.loadCase = function () {
        $scope.commitsExerciseCaseView = $sce.trustAsHtml("<p>Carregando</p>");
        if ($scope.caseId == -1) {
            return;
        }
        CommitsExerciseCasesService.loadCommitExerciseCase($scope.caseId).then(function (response) {
            $scope.commitsExerciseCaseView = $sce.trustAsHtml(response);

//                $scope.apply();
        });
    }
}]);

runcodesApp.controller('GradesController', ['$scope', function ($scope) {
    $scope.notDelivered = "0.0";
}]);

runcodesApp.controller('MultipleUploadController', ['$scope', function ($scope) {
    $scope.progress = 0;
    $scope.files = [];

    $scope.addExistentFile = function (name,id,size) {
        $scope.files.push({existent: true,realname: name, size: size, id: id, remove: false});
    };

    $scope.removeExistentFile = function (index) {
        $scope.files[index].remove = true;
    };

    $scope.undoRemoveExistentFile = function (index) {
        $scope.files[index].remove = false;
    };

    $scope.removeFile = function (index) {
        $scope.files.splice(index,1);
    };

    $scope.loadFileUpload = function (element,dropzone) {
        dropzone = dropzone ||"#files-drop-zone";

        $(element).fileupload({
            dataType: 'json',
            dropZone: $(dropzone),
            add: function (e, data) {
                data.submit();
                //$scope.filename = data.files[0].name + " - " + data.files[0].size + " bytes";
                //$scope.data = data;
                //$scope.files.push({filename: data.files[0].name, size: data.files[0].size, data: data});
            },
            start: function (e, data) {

            },
            fail: function (e, data) {
                if (data.jqXHR.status == 413) {
                    alert("O tamanho do arquivo selecionado é maior que o máximo permitido");
                    $scope.progress = 0;
                    $scope.uploadMessage = $scope.uploadText;
                } else {
                    if (data.textstatus != "parsererror") {
                        alert("Ocorreu uma falha e seu arquivo não foi enviado, por favor tente novamente. Se este erro persistir, entre com contato com runcodes@icmc.usp.br");
                    }
                }
            },
            progressall: function (e, data) {
                var prog = parseInt(data.loaded / data.total * 100, 10);
                $scope.uploadMessage = $scope.uploadingText + " (" + prog + "%)";
                $scope.progress = prog;
            },
            done: function (e, data) {
                $.each(data.result.files, function (index, file) {
                    if(file.error){

                    } else {
                        $scope.files.push(file);
                        $scope.progress=0;
                    }
                });
                $scope.uploadMessage = $scope.uploadText;
                //if(data.result.files[0].error){
                //    $scope.alertMessage = data.result.files[0].error;
                //    $scope.filename = "";
                //    $scope.showFileInfo = false;
                //    $scope.progress = 0;
                //    $scope.data = null;
                //    $scope.submitted = false;
                //    $scope.submitButton = $scope.submitButtonDefault;
                //}else{
                //    $scope.submitButton = $scope.submitButtonReloading;
                //    location.reload(true);
                //}
            }
        });
    };

}]);

runcodesApp.controller('FormExerciseController', ['$scope','FormExerciseService', function ($scope,FormExerciseService) {
    $scope.allowedFiles = [];
    $scope.loading = false;
    var isFirstLoad = true;

    $('.chosen-select').chosen({
        width: '100%'
    });

    $scope.reloadFileTypes = function () {
        $scope.loading = true;
        FormExerciseService.loadAllowedFilesByType($scope.filesType).then(function (files) {
            $scope.allowedFiles = files;
            if (isFirstLoad) {
                isFirstLoad = false;
                setTimeout(function () {
                    $(".chosen-select").val($scope.initSelection);
                },10);
            }
            setTimeout(function () {
                $(".chosen-select").trigger("chosen:updated");
                $scope.loading = false;
            },500);
        });
    };
}]);

runcodesApp.service('FormExerciseService', ['$http',function ($http) {
    this.loadAllowedFilesByType = function (type) {
        var request = $http.post("/AllowedFiles/getAllowedFilesList/" + type);
        return (request.then(handleSuccess));
        function handleSuccess(response) {
            return(response.data);
        }
    };
}]);

runcodesApp.controller('FormOfferingController', ['$scope','FormOfferingService', function ($scope,FormOfferingService) {
    $scope.coursesList = [];
    $scope.searchString = "";
    $scope.loading = false;

    $scope.loadCourses = function () {
        $scope.loading = true;
        $scope.showCourses = true;
        FormOfferingService.loadCoursesWithSearchString($scope.searchString).then(function (courses) {
            $scope.coursesList = courses;
            $scope.loading = false;
            $scope.showCourses = true;
        });
    }
}]);

runcodesApp.service('FormOfferingService', ['$http',function ($http) {
    this.loadCoursesWithSearchString = function (search) {
        var request = $http.post("/Courses/coursesList/" + search);
        return (request.then(handleSuccess));
        function handleSuccess(response) {
            return(response.data);
        }
    };
}]);

runcodesApp.controller('FormExerciseCaseController', ['$scope', function ($scope) {

}]);

runcodesApp.controller('FormProfileController', ['$scope','FormProfileService', function ($scope,FormProfileService) {
    $scope.loadUniversityInfo = function () {
        FormProfileService.loadUniversityIdentifierText($scope.university).then(function (universityInfo) {
            $scope.identifierText = universityInfo;
        });
    }
}]);

runcodesApp.service('FormProfileService', ['$http',function ($http) {
    this.loadUniversityIdentifierText = function (university_id) {
        var request = $http.post("/Universities/getIdentifierText/" + university_id);
        return (request.then(handleSuccess));
        function handleSuccess(response) {
            return(response.data);
        }
    };
}]);

runcodesApp.controller('ExerciseProfessorViewController', ['$scope','$sce','ExerciseProfessorViewService', function ($scope,$sce,ExerciseProfessorViewService) {
    $scope.loading = false;
    $scope.dataset = [{ data: [], yaxis: 1, label: "" }];
    $scope.options = {
        legend: {
            container: "#legend",
            show: true
        },
        bars: {
            show: true,
            barWidth: 0.6,
            align: "center"
        },
        colors: ["#FFFFFF"],
        xaxis: {
            min: 0
        },
        yaxis: {
            min: 0,
            max: 101,
            ticks: 10
        }
    };

    $scope.loadCasesStats = function () {
        ExerciseProfessorViewService.loadCaseChartData($scope.exercise).then(function (chart) {
            $scope.dataset[0].data = chart.series[0].data;
            $scope.dataset[0].label = chart.series[0].name;
            $scope.options.xaxis.ticks = chart.labelsX;
            $scope.options.xaxis.max = chart.labelsX.length + 1;
            $scope.options.colors = chart.colors;
        });
    };

    $scope.loadStudentCommits = function () {
        if ($scope.studentCommits == -1) {
            $scope.studentCommitsContent = $sce.trustAsHtml("");
            return;
        };
        $scope.loading = true;
        ExerciseProfessorViewService.loadStudentCommitsInfo($scope.exercise,$scope.studentCommits).then(function (studentCommitsHtml) {
            $scope.studentCommitsContent = $sce.trustAsHtml(studentCommitsHtml);
            $scope.loading = false;
        });
    };

    $scope.loadCasesTable = function () {
        $scope.loading = true;
        ExerciseProfessorViewService.loadCasesData($scope.exercise).then(function (loadCasesData) {
            $scope.casesContent = $sce.trustAsHtml(loadCasesData);
            $scope.loading = false;
        });
    };
}]);

runcodesApp.service('ExerciseProfessorViewService', ['$http',function ($http) {
    this.loadStudentCommitsInfo = function (exercise,student) {
        var request = $http.post("/Exercises/participantCommits/" + exercise + "/" + student);
        return (request.then(handleSuccess));

    };

    this.loadCasesData = function (exercise) {
        var request = $http.post("/Exercises/casesTable/" + exercise);
        return (request.then(handleSuccess));

    };

    this.loadCaseChartData = function (exercise) {
        var request = $http.post("/Exercises/stats/" + exercise);
        return (request.then(handleSuccess));
    };

    function handleSuccess(response) {
        return(response.data);
    }
}]);

runcodesApp.controller('OfferingController', ['$scope','$sce','OfferingService', function ($scope,$sce,OfferingService) {
    $scope.professorsAndAssistants= [];
    $scope.loading = false;

    $scope.setProfessor = function () {
        $scope.loading = true;
        OfferingService.setUserAsProfessor($scope.offeringId,$scope.participant).then(function () {
            $scope.loadProfessorsAndAssistantsTable();
        });
    };
    $scope.setAssistant = function () {
        $scope.loading = true;
        OfferingService.setUserAsAssistant($scope.offeringId,$scope.participant).then(function () {
            $scope.loadProfessorsAndAssistantsTable();
        });
    };
    $scope.setStudent = function (student) {
        $scope.loading = true;
        OfferingService.setUserAsStudent($scope.offeringId,student).then(function () {
            $scope.loadProfessorsAndAssistantsTable();
        });
    };

    $scope.loadProfessorsAndAssistantsTable = function () {
        OfferingService.loadProfessorsAndAssistants($scope.offeringId).then(function (professorsAndAssitantsInfo) {
            $scope.professorsAndAssistants = professorsAndAssitantsInfo;
            $scope.loading = false;
        });
    }
}]);

runcodesApp.service('OfferingService', ['$http',function ($http) {

    this.setUserAsAssistant = function (offering_id,student) {
        var request = $http.post("/Offerings/setAssistant/" + offering_id + "/" + student);
            return (request.then(handleSuccess));
    };

    this.setUserAsProfessor = function (offering_id,student) {
        var request = $http.post("/Offerings/setProfessor/" + offering_id + "/" + student);
        return (request.then(handleSuccess));
    };

    this.setUserAsStudent = function (offering_id,student) {
        var request = $http.post("/Offerings/setStudent/" + offering_id + "/" + student);
        return (request.then(handleSuccess));
    };

    this.loadProfessorsAndAssistants = function (offering_id) {
        var request = $http.post("/Offerings/getProfessorsAndAssistantsList/" + offering_id);
        return (request.then(handleSuccess));
    };

    function handleSuccess(response) {
        return(response.data);
    }
}]);

runcodesApp.controller('LastCommitController', ['$scope','$sce','$timeout','LastCommitService', function ($scope,$sce,$timeout,LastCommitService) {
    $scope.professorsAndAssistants= [];
    $scope.loading = false;
    $scope.count = 0;
    $scope.commitsExerciseCaseDetails = $sce.trustAsHtml("");

    $scope.startRefreshing = function () {
        if ($scope.commit.status.value < 4) {
            $scope.loading = true;
            $timeout(function () {
                LastCommitService.loadCommitInfo($scope.commit.id).then(function (commitInfo) {
                    $scope.commit = commitInfo.commit;
                    if (commitInfo.commit.status.value < 4) {
                        $scope.startRefreshing();
                    } else {
                        if (commitInfo.commit.status.value == 4 || commitInfo.commit.status.value == 5) {
                            loadCommitsCases();
                        }
                        $scope.loading = false;
                    }
                });
            }, 2000);
        }
    };

    function loadCommitsCases () {
        LastCommitService.loadCommitCases($scope.commit.id).then(function (commitCasesInfo) {
            $scope.commitsExerciseCaseDetails = $sce.trustAsHtml(commitCasesInfo);
        });
    }

}]);

runcodesApp.service('LastCommitService', ['$http',function ($http) {

    this.loadCommitInfo = function (commit_id) {
        var request = $http.post("/Commits/commitInfo/" + commit_id);
        return (request.then(handleSuccess));
    };

    this.loadCommitCases = function (commit_id) {
        var request = $http.post("/Commits/casesInfo/" + commit_id);
        return (request.then(handleSuccess));
    };
    //

    function handleSuccess(response) {
        return(response.data);
    }
}]);

runcodesApp.controller('ExerciseDescriptionController', ['$scope','$sce', function ($scope,$sce) {
    $scope.area = "";
    $scope.loadMarkdownDescription = function () {
        var demo = function(converter) {
            return [
                {
                    type    : 'html',
                    regex   : '<table>',
                    replace : '<table class="table table-hover table-striped table-bordered">'
                },
                {
                    type    : 'html',
                    regex   : '<pre>',
                    replace : '<pre class="markdown-pre">'
                }
            ];
        };
        var converter = new showdown.Converter({tables: true,simplifiedAutoLink: true,extensions: [demo]});
        $scope.html = $sce.trustAsHtml(converter.makeHtml($(".markdown" + $scope.area).val()));
    }
}]);

$(document).ready(function() {
    moment.locale('pt-br');
    $('.dropdown').on('show.bs.dropdown', function(e) {     $(this).find('.dropdown-menu').first().stop(true, true).slideDown(); });
    $('.dropdown').on('hide.bs.dropdown', function(e) { $(this).find('.dropdown-menu').first().stop(true, true).slideUp(); });
    $('[data-toggle="tooltip"]').tooltip();
    $('.summernote').summernote();

    $('.chosen-unique-select').chosen({
        width: '100%'
    });

    $('.datepicker').datetimepicker({
        format: "DD/MM/YYYY",
        showTodayButton: false,
        //minDate: new Date(),
        icons: {
            time: 'fa fa-clock-o',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-calendar-o',
            clear: 'fa fa-eraser'
        }
    });
    $('.datetimepicker').datetimepicker({
        format: "DD/MM/YYYY HH:mm:ss",
        sideBySide: true,
        showTodayButton: true,
        //minDate: new Date(),
        icons: {
            time: 'fa fa-clock-o',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-calendar-o',
            clear: 'fa fa-eraser'
        }
    });

    hljs.initHighlightingOnLoad();

    var delay = 700;
    $(".btn-hide-panel").on('click', function() {
        var panel = $(this).attr('data-panel');
        $("#panel-" + panel).animate({
            height: 0,
            padding: 0
        }, delay, function() {
            $.getJSON("/cookies/hidePanel/" + panel);
            $("#btn-hide-" + panel).hide();
            $("#btn-show-" + panel).show();
        });
    });
    $(".btn-show-panel").on('click', function() {
        var panel = $(this).attr('data-panel');
        var curHeight = $("#panel-" + panel).height();
        var autoHeight = $("#panel-" + panel).css('height', 'auto').height();
        if (panel == "home-stats") {
            autoHeight = 310;
        }
        if (panel == "home-servers") {
            autoHeight += 20;
        }
        $("#panel-" + panel).height(curHeight).animate({
            height: autoHeight,
            padding: 10
        }, delay, function() {
            $.getJSON("/cookies/showPanel/" + panel);
            $("#btn-hide-" + panel).show();
            $("#btn-show-" + panel).hide();
        });
    });
    $(".btn-toggle-panel").on('click', function(e) {
        e.preventDefault();
        var panel = $(this).attr('data-panel');
        if ($("#panel-" + panel).hasClass("panel-hide")) {
            $("#panel-" + panel).removeClass("panel-hide");
            var curHeight = $("#panel-" + panel).height();
            var autoHeight = $("#panel-" + panel).css('height', 'auto').height();
            $("#panel-" + panel).height(curHeight).animate({
                height: autoHeight,
                padding: 10
            }, delay);
        } else {
            $("#panel-" + panel).animate({
                height: 0,
                padding: 0
            }, delay, function() {
                $("#panel-" + panel).addClass("panel-hide");
            });
        }
    });

    if ( window.addEventListener ) {
        var kkeys = [], konami = "38,38,40,40,37,39,37,39,66,65";
        window.addEventListener("keydown", function(e){
            kkeys.push( e.keyCode );
            if ( kkeys.toString().indexOf( konami ) >= 0 ) {
                console.log("Konami");
                kkeys = [];
            }
            if ((e.keyCode < 37 || e.keyCode > 40) && (e.keyCode != 66 && e.keyCode != 65)) {
                kkeys = [];
            }
        }, true);
    };

    $(".main-loading").addClass("hidden");
    $(".main-content").removeClass("hidden");
    $("#nav-right-menu").removeClass("hidden");
});

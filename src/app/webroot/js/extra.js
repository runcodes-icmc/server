

runcodesApp.controller('StatsController', ['$scope','$sce','$interval','StatsService', function ($scope,$sce,$interval,StatsService) {
    $scope.infographic = {};
    $scope.infographic.box1 = {};
    $scope.infographic.box2 = {};
    $scope.infographic.box3 = {};
    $scope.loading = true;
    $scope.options = {};
    $scope.features = {};
    $scope.series = {};

    var loadVisitsStats = function () {
        $scope.options = {
            renderer: 'line',
            interpolation: 'linear',
            height: 280
        };
        $scope.features = {
            //palette: 'colorwheel',
            xAxis: {
                orientation: 'bottom'
            },
            yAxis: {
                tickFormat: Rickshaw.Fixtures.Number.formatKMBT
            },
            legend: {
                toggle: true,
                highlight: true
            },
            hover: {
                xFormatter: function(x) {
                    return moment(parseInt(x),'X').format('DD/MM/YYYY');
                },
                yFormatter: function(y) {
                    return y;
                }
            }
        };
        $scope.series = [{
            data: [{"x":0,"y":0}]
        }];

        StatsService.loadVisitsChartData().then(function (stats) {
            $scope.series = stats.chart;
            $scope.infographic = stats.infographic;
            $scope.loading = false;
        });
    };

    var loadDiskStats = function () {
        $scope.options = {
            renderer: 'line',
            interpolation: 'linear',
            height: 280,
            max: 100
        };
        $scope.features = {
            //palette: 'colorwheel',
            xAxis: {
                orientation: 'bottom'
            },
            yAxis: {
                tickFormat: Rickshaw.Fixtures.Number.formatKMBT,
                min: 0,
                max: 100
            },
            legend: {
                toggle: true,
                highlight: true
            },
            hover: {
                xFormatter: function(x) {
                    return moment(parseInt(x),'X').format('DD/MM/YYYY HH:mm');
                },
                yFormatter: function(y) {
                    return y.toFixed(2) + "%";
                }
            }
        };
        $scope.series = [{
            data: [{"x":0,"y":0}]
        }];

        StatsService.loadDiskChartData().then(function (stats) {
            $scope.series = stats.chart;
            $scope.infographic = stats.infographic;
            $scope.loading = false;
        });
    };

    var loadCommitsStats = function () {
        $scope.options = {
            //renderer: 'line',
            interpolation: 'linear',
            height: 280
        };
        $scope.features = {
            //palette: 'colorwheel',
            xAxis: {
                orientation: 'bottom'
            },
            yAxis: {
                tickFormat: Rickshaw.Fixtures.Number.formatKMBT
            },
            legend: {
                toggle: true,
                highlight: true
            },
            hover: {
                xFormatter: function(x) {
                    return moment(parseInt(x),'X').format('DD/MM/YYYY');
                },
                yFormatter: function(y) {
                    return y;
                }
            }
        };
        $scope.series = [{
            data: [{"x":0,"y":0}]
        }];

        StatsService.loadCommitsChartData().then(function (series) {
            $scope.series = series;
            $scope.loading = false;
        });

        StatsService.loadCommitsInfographicData().then(function (data) {
            $scope.infographic = data;
        });
    };

    $scope.loadStats = function () {
        $scope.loading = true;
        if ($scope.source == 1) {
            loadCommitsStats();
        }else if ($scope.source == 2) {
            loadVisitsStats();
        }else if ($scope.source == 3) {
            loadDiskStats();
        }
    }
}]);
runcodesApp.controller('InfographicsController', ['$scope','$sce','$interval','StatsService', function ($scope,$sce,$interval,StatsService) {
    $scope.queue = 0;
    $scope.execution = 0;
    $scope.users = 0;
    $scope.servers = {};
    $scope.servers.web = 0;
    $scope.servers.db = 0;
    $scope.servers.compiler = 0;

    $scope.updateInfographics = $interval(function () {
        StatsService.loadHomeInfographicData().then(function (data) {
            $scope.queue = data.queue;
            $scope.execution = data.execution;
            $scope.users = data.users;
            $scope.servers.web = data.servers.web;
            $scope.servers.db = data.servers.db;
            $scope.servers.compiler = data.servers.compiler;
        });
    },2000);

}]);

runcodesApp.controller('ServersController', ['$scope','$sce','$interval','StatsService', function ($scope,$sce,$interval,StatsService) {
    $scope.servers = [];

    $scope.updateInfographics = $interval(function () {
        StatsService.loadHomeServersData().then(function (data) {
            $scope.servers = data;
        });
    },10000);

}]);

runcodesApp.controller('AddCoursesForm', ['$scope','$sce', function ($scope,$sce) {
    $scope.batch = false;
}]);

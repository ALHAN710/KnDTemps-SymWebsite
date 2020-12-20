/**
 * Theme: Frogetor - Responsive Bootstrap 4 Admin Dashboard
 * Author: MannatThemes
 * Module/App: App.js
 */


var dash_spark_1 = {

  chart: {
    type: 'area',
    height: 80,
    sparkline: {
      enabled: true
    },
  },
  stroke: {
    curve: 'smooth',
    width: 2
  },
  fill: {
    opacity: 0.05,
  },
  series: [{
    data: [4, 8, 5, 10, 4, 16, 5, 11, 6, 11, 30, 10, 13, 4, 6, 3, 6]
  }],
  yaxis: {
    min: 0
  },
  colors: ['#fbb624'],
}
new ApexCharts(document.querySelector("#dash_spark_1"), dash_spark_1).render();


//turnOver-colunm-Chart

var options = {

  chart: {
    height: 357,
    animations: {
      enabled: false
    },
    sparkline: {
      enabled: true
    },
    type: "bar"
  },
  noData: {
    text: 'data not yet avalaible',
    align: 'center',
    verticalAlign: 'middle',
    offsetX: 0,
    offsetY: 0,
    style: {
      color: undefined,
      fontSize: '14px',
      fontFamily: undefined
    }
  },
  plotOptions: {
    bar: {
      horizontal: false,
      //endingShape: "rounded",
      columnWidth: "40%"
    }
  },
  dataLabels: {
    enabled: false
  },
  stroke: {
    show: true,
    width: 2,
    colors: ["transparent"]
  },
  colors: ["#1ecab8"],
  series: [{
    name: "Revenue",
    data: []
  }
  ],
  xaxis: {
    categories: [],
    crosshairs: {
      show: false,
    },
  },
  fill: {
    opacity: 0.5
  },

  tooltip: {
    y: {
      formatter: function (val) {
        return val + " XAF"
      },
    },
  }

}
var turnOverColumnChart = new ApexCharts(
  document.querySelector("#turnOverColumnChart"),
  options
);

turnOverColumnChart.render();
//payment-widget


var options = {
  chart: {
    height: 400,
    type: 'donut',
    offsetY: -15
  },
  series: [0, 0],
  legend: {
    show: true,
    position: 'top',
    horizontalAlign: 'center',
    verticalAlign: 'middle',
    floating: false,
    fontSize: '14px',
    offsetX: 0,
    offsetY: 0
  },
  labels: ["Recettes", "Dépenses"],
  colors: ["#00dd9f", "#f65f4d"],
  responsive: [{
    breakpoint: 600,
    options: {
      chart: {
        height: 400
      },
      legend: {
        show: true
      },
    }
  }],
  tooltip: {
    y: {
      formatter: function (val) {
        return val + ' XAF'
      },
      title: {
        formatter: function (seriesName) {
          return String(seriesName).substring(0, String(seriesName).indexOf(' ') + 1)
        }
      }
    }
  },
}

var fluxTresorerieDonutChart = new ApexCharts(
  document.querySelector("#flux_tresorerie"),
  options
);

fluxTresorerieDonutChart.render();

/*
//amountRecettes-colunm-Chart

var options = {

  chart: {
    height: 80,
    animations: {
      enabled: false
    },
    sparkline: {
      enabled: true
    },
    type: "bar"
  },
  noData: {
    text: 'data not yet avalaible',
    align: 'center',
    verticalAlign: 'middle',
    offsetX: 0,
    offsetY: 0,
    style: {
      color: undefined,
      fontSize: '14px',
      fontFamily: undefined
    }
  },
  plotOptions: {
    bar: {
      horizontal: false,
      //endingShape: "rounded",
      columnWidth: "40%"
    }
  },
  dataLabels: {
    enabled: false
  },
  stroke: {
    show: true,
    width: 2,
    colors: ["transparent"]
  },
  colors: ["#1ecab8"],
  series: [{
    name: "Recettes",
    data: []
  }
  ],
  xaxis: {
    categories: [],
    crosshairs: {
      show: false,
    },
  },
  fill: {
    opacity: 0.5
  },

  tooltip: {
    y: {
      formatter: function (val) {
        return val + " XAF"
      },
    },
  }

}
var amountRecettesColumnChart = new ApexCharts(
  document.querySelector("#amountRecettesColumnChart"),
  options
);

amountRecettesColumnChart.render();

//expenses-colunm-Chart

var options = {

  chart: {
    height: 80,
    animations: {
      enabled: false
    },
    sparkline: {
      enabled: true
    },
    type: "bar"
  },
  noData: {
    text: 'data not yet avalaible',
    align: 'center',
    verticalAlign: 'middle',
    offsetX: 0,
    offsetY: 0,
    style: {
      color: undefined,
      fontSize: '14px',
      fontFamily: undefined
    }
  },
  plotOptions: {
    bar: {
      horizontal: false,
      //endingShape: "rounded",
      columnWidth: "40%"
    }
  },
  dataLabels: {
    enabled: false
  },
  stroke: {
    show: true,
    width: 2,
    colors: ["transparent"]
  },
  colors: ["#dc3545"],
  series: [{
    name: "Dépenses",
    data: []
  }
  ],
  xaxis: {
    categories: [],
    crosshairs: {
      show: false,
    },
  },
  fill: {
    opacity: 0.5
  },

  tooltip: {
    y: {
      formatter: function (val) {
        return val + " XAF"
      },
    },
    x: {
      formatter: function (val) {
        return val + " h"
      },
    },
  }

}
var expensesColumnChart = new ApexCharts(
  document.querySelector("#expensesColumnChart"),
  options
);

expensesColumnChart.render();

//dash-radialBar

var options = {
    chart: {
        height: 180,
        type: "radialBar",
      },

      series: [67],
      colors: ["#20E647"],
      plotOptions: {
        radialBar: {
          hollow: {
            margin: 0,
            size: "60%",
            background: "#293450"
          },
          track: {
            dropShadow: {
              enabled: true,
              top: 2,
              left: 0,
              blur: 4,
              opacity: 0.15
            }
          },
          dataLabels: {
            name: {
              offsetY: -5,
              color: "#fff",
              fontSize: "14px",
            },
            value: {
                offsetY: 5,
              color: "#fff",
              fontSize: "14px",
              show: true
            }
          }
        }
      },
      fill: {
        type: "gradient",
        gradient: {
          shade: "dark",
          type: "vertical",
          gradientToColors: ["#87D4F9"],
          stops: [0, 100]
        }
      },
      stroke: {
        lineCap: "round"
      },
      labels: ["Progress"]
    };

    var chart = new ApexCharts(document.querySelector("#d1-radialBarMap"), options);

    chart.render();



//Dash-Map

$('#world-map-markers').vectorMap({
  map : 'world_mill_en',
  scaleColors : ['#eff0f1', '#eff0f1'],
  normalizeFunction : 'polynomial',
  hoverOpacity : 0.7,
  hoverColor : false,
  regionStyle : {
      initial : {
          fill : '#eff0f1'
      }
  },

  markerStyle: {
    initial: {
      stroke: "transparent"
    },
    hover: {
      stroke: "rgba(112, 112, 112, 0.30)"
    }
  },
  backgroundColor : 'transparent',

  markers: [
    {
      latLng: [37.090240, -95.712891],
      name: "USA",
      style: {
        fill: "#f93b7a"
      }
    },
    {
      latLng: [71.706940, -42.604301],
      name: "Greenland",
      style: {
        fill: "#f0961f"
      }
    },
    {
      latLng: [-21.943369, 123.102198],
      name: "Australia",
      style: {
        fill: "#5766da"
      }
    }
  ],
  series: {
    regions: [{
        values: {
            "AU": '#c4c9f2',
            "US": '#fdcede',
            "GL": '#fae1be',
        },
        attribute: 'fill'
    }]
},
});
*/



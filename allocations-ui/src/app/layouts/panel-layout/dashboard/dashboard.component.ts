/**
 * Copyrights Allocations 2021. All rights reserved
 *
 * The code, text and other elements of this application/file is copyrighted
 * You may not remove any copyright or other proprietary notices contained in this file
 * The rights granted to you use this application in your organization for your
 * business/personal purpose and not to sell or modify
 *
 * Developed by: Mohamed Asif
 * Email: mohamedasif18@gmail.com
 */
import { Component, OnInit, OnDestroy } from '@angular/core';
import { Title } from '@angular/platform-browser';
import { Subscription } from 'rxjs';
import { faUserCheck, faNetworkWired, faTachometerAlt, faClock,
  faCompactDisc, faMemory } from '@fortawesome/free-solid-svg-icons';
import { Chart } from 'angular-highcharts';
import * as Highcharts from 'highcharts';
import * as _ from 'lodash';
import { DashboardService } from '../../../_services';

Highcharts.setOptions({
  time: {
    useUTC: false
  }
});

declare interface LineChartData {
  rx: number;
  tx: number;
  status: boolean;
  message: string;
}

declare interface DashboardData {
  activeUsersCount?: number;
  dhcpLeasesCount?: number;
  cpuLoad?: string;
  uptime?: string;
  freeMemory?: string;
  freeHDDSpace?: string;
  lineChartData?: LineChartData;
}

declare interface FormDependencyData {
  interfaces: Array<string>;
}

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss']
})
export class DashboardComponent implements OnInit, OnDestroy {

  public isFormLoading: boolean = true;
  public dashboardData: DashboardData = {
    activeUsersCount: 0,
    dhcpLeasesCount: 0,
    cpuLoad: '0',
    uptime: '0',
    freeMemory: '0',
    freeHDDSpace: '0',
    lineChartData: null
  };
  private subscriptions: Subscription[] = [];
  public faIcons = {faUserCheck, faNetworkWired, faTachometerAlt, faClock, faCompactDisc, faMemory};
  public chart: Chart;
  public options: Highcharts.Options;

  constructor(
    private titleService: Title,
    private dashboardService: DashboardService) {
    this.titleService.setTitle('Dashboard');
  }

  ngOnInit() {
    this.getDashboardData();
  }

  private initLineChart() {
    this.options = {
      chart: {
        type: 'line'
      },
      title: {
        text: ''
      },
      credits: {
        enabled: false
      },
      xAxis: {
        type: 'datetime',
        tickPixelInterval: 150
      },
      series: [{
        name: 'RX',
        data: [],
        type: 'line'
      },
      {
        name: 'TX',
        data: [],
        type: 'line'
      }]
    };
    this.chart = new Chart(this.options);
  }

  getDashboardData() {
    this.initLineChart();
    this.subscriptions.push(this.dashboardService.getDashboardData()
      .subscribe((response) => {
        this.isFormLoading = false;
        this.populateDashboard(response.data);
      }));
  }

  populateDashboard(responseData: DashboardData): void {
    this.dashboardData = responseData;
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}

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
import { faFileContract, faTruckPickup, faTruck, faTruckMoving, faTruckLoading, faMapMarkerAlt, 
  faMapMarkedAlt } from '@fortawesome/free-solid-svg-icons';
import * as _ from 'lodash';
import { DashboardService } from '../../../_services';

declare interface DashboardData {
  allAllocationsCount: number;
  notAllocatedCount: number;
  allocatedCount: number;
  deliveredCount: number;
  yardsCount: number;
  destinationsCount: number;
}

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss']
})
export class DashboardComponent implements OnInit, OnDestroy {

  public isFormLoading: boolean = true;
  public dashboardData: DashboardData = {
    allAllocationsCount: 0,
    notAllocatedCount: 0,
    allocatedCount: 0,
    deliveredCount: 0,
    yardsCount: 0,
    destinationsCount: 0,
  };
  private subscriptions: Subscription[] = [];
  public faIcons = { faFileContract, faTruckPickup, faTruck, faTruckMoving, faTruckLoading, faMapMarkerAlt, faMapMarkedAlt };

  constructor(
    private titleService: Title,
    private dashboardService: DashboardService) {
    this.titleService.setTitle('Dashboard');
  }

  ngOnInit() {
    this.getDashboardData();
  }

  getDashboardData() {
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

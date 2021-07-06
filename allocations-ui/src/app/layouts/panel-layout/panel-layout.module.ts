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
import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { ComponentsModule } from '../../_components';
import { ChartModule } from 'angular-highcharts';
import { PanelLayoutComponent } from './panel-layout.component';
import { DashboardComponent } from './dashboard';
import { PanelLayoutRoutes } from './panel-layout.routes';
import { AuthGuard } from '../../_guards';
import { DashboardResolver } from '../../_classes';

import { AllocationsComponent } from './allocations';
import { AllocationsListComponent } from './allocations-list';
import { YardsComponent } from './yards';
import { DestinationsComponent } from './destinations';

import { InstancesListComponent } from './instances-list';
import { MaterialModule } from '../../material.module';
import { InstanceComponent } from './instance';
import { UsersListComponent } from './users-list';
import { ClientComponent } from './client';
import { ClientsListComponent } from './clients-list';
import { SettingsComponent } from './settings';
import { DataUsageLimitsComponent } from './data-usage-limits';
import { StaffsListComponent } from './staffs-list';
import { RoomComponent } from './room';
import { RoomsListComponent } from './rooms-list';
import { SendOtpComponent } from './send-otp';
import { DisableControlDirective, UppercaseControlDirective } from '../../_directives';
import { ActiveUsersListComponent } from './active-users-list';
import { ProfileListComponent } from './profile-list';
import { ProfileComponent } from './profile';
import { HostsListComponent } from './hosts-list';
import { IpBindingComponent } from './ip-binding';
import { IpBindingListComponent } from './ip-binding-list';
import { OtpLogListComponent } from './otp-log-list';
import { WifiUsageLogListComponent } from './wifi-usage-log-list';
import { WifiUserSettingsComponent } from './wifi-user-settings';
import { AccessPointManagerComponent } from './access-point-manager';
import { AccessPointManagerListComponent } from './access-point-manager-list';
import { WebLogsComponent } from './web-logs/web-logs.component';

@NgModule({
  imports: [
    BrowserModule,
    CommonModule,
    ComponentsModule,
    MaterialModule,
    ChartModule,
    FormsModule,
    FontAwesomeModule,
    ReactiveFormsModule,
    RouterModule.forChild(PanelLayoutRoutes)
  ],
  declarations: [
    PanelLayoutComponent,
    DashboardComponent,
    InstancesListComponent,
    InstanceComponent,
    UsersListComponent,
    ClientComponent,
    ClientsListComponent,
    SettingsComponent,
    DataUsageLimitsComponent,
    
    AllocationsComponent,
    AllocationsListComponent,
    YardsComponent,
    DestinationsComponent,

    StaffsListComponent,
    RoomComponent,
    RoomsListComponent,
    SendOtpComponent,
    DisableControlDirective,
    UppercaseControlDirective,
    ActiveUsersListComponent,
    ProfileListComponent,
    ProfileComponent,
    HostsListComponent,
    IpBindingComponent,
    IpBindingListComponent,
    OtpLogListComponent,
    WifiUsageLogListComponent,
    WifiUserSettingsComponent,
    AccessPointManagerComponent,
    AccessPointManagerListComponent,
    WebLogsComponent,
    AllocationsComponent
  ],
  providers: [
    AuthGuard,
    DashboardResolver
  ]
})

export class PanelLayoutModule {}

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
import { Routes } from '@angular/router';
import { PanelLayoutComponent } from './panel-layout.component';
import { DashboardComponent } from './dashboard';
import { ClientComponent } from './client';
import { ClientsListComponent } from './clients-list';
import { InstancesListComponent } from './instances-list';
import { InstanceComponent } from './instance';
import { UsersListComponent } from './users-list';
import { DataUsageLimitsComponent } from './data-usage-limits';

import { AllocationsComponent } from './allocations';
import { AllocationsListComponent } from './allocations-list';
import { AllocatedListComponent } from './allocated-list';
import { DeliveredListComponent } from './delivered-list'
import { YardsComponent } from './yards';
import { DestinationsComponent } from './destinations';

import { StaffsListComponent } from './staffs-list';
import { SendOtpComponent } from './send-otp';
import { RoomComponent } from './room';
import { RoomsListComponent } from './rooms-list';
import { SettingsComponent } from './settings';
import { AuthenticationService } from '../../_services';
import { AuthGuard } from '../../_guards';
import { DashboardResolver } from '../../_classes';
import { ActiveUsersListComponent } from './active-users-list';
import { ProfileComponent } from './profile';
import { ProfileListComponent } from './profile-list';
import { HostsListComponent } from './hosts-list';
import { IpBindingComponent } from './ip-binding';
import { IpBindingListComponent } from './ip-binding-list';
import { OtpLogListComponent } from './otp-log-list';
import { WifiUsageLogListComponent } from './wifi-usage-log-list';
import { WifiUserSettingsComponent } from './wifi-user-settings';
import { AccessPointManagerComponent } from './access-point-manager';
import { AccessPointManagerListComponent } from './access-point-manager-list';
import { WebLogsComponent } from './web-logs';

export const PanelLayoutRoutes: Routes = [
  {
    path: 'panel',
    component: PanelLayoutComponent,
    resolve: { userInfo: AuthenticationService },
    children: [
      {
        path: '',
        redirectTo: 'dashboard',
        pathMatch: 'full'
      },
      {
        path: 'dashboard',
        component: DashboardComponent,
        canActivate: [AuthGuard],
        resolve: {
          dashboardData: DashboardResolver
        },
        data: {
          title: 'Dashboard',
          pageName: 'dashboard'
        }
      },
      {
        path: 'client',
        redirectTo: 'client/'
      },
      {
        path: 'client/:id',
        component: ClientComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Client',
          pageName: 'client'
        }
      },
      {
        path: 'clients-list',
        component: ClientsListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Clients List',
          pageName: 'clientsList'
        }
      },
      {
        path: 'room',
        component: RoomComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Add Room',
          pageName: 'room'
        }
      },
      {
        path: 'send-otp',
        component: SendOtpComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Send OTP',
          pageName: 'sendOtp'
        }
      },
      {
        path: 'send-otp/:id',
        component: SendOtpComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Send OTP',
          pageName: 'sendOtp'
        }
      },
      {
        path: 'active-users-list',
        component: ActiveUsersListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'List Active Users',
          pageName: 'activeUsersList'
        }
      },
      {
        path: 'profile',
        component: ProfileComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Profile',
          pageName: 'profile'
        }
      },
      {
        path: 'profiles-list',
        component: ProfileListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Profiles List',
          pageName: 'profilesList'
        }
      },
      {
        path: 'hosts-list',
        component: HostsListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Hosts List',
          pageName: 'hostsList'
        }
      },
      {
        path: 'ip-binding',
        component: IpBindingComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'By Pass User',
          pageName: 'ipBinding'
        }
      },
      {
        path: 'otp-log',
        component: OtpLogListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'OTP Log',
          pageName: 'otpLog'
        }
      },
      {
        path: 'wifi-usage-log',
        component: WifiUsageLogListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'WiFi Usage Log',
          pageName: 'wifiUsageLog'
        }
      },
      {
        path: 'ip-binding-list',
        component: IpBindingListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'By Pass Users List',
          pageName: 'ipBindingList'
        }
      },
      {
        path: 'rooms-list',
        component: RoomsListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'List Rooms',
          pageName: 'roomsList'
        }
      },
      {
        path: 'instance',
        redirectTo: 'instance/'
      },
      {
        path: 'instance/:id',
        component: InstanceComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Instance',
          pageName: 'instance'
        }
      },
      {
        path: 'instances-list',
        component: InstancesListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Instances List',
          pageName: 'instancesList'
        }
      },
      {
        path: 'staffs-list',
        component: StaffsListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Staffs List',
          pageName: 'staffsList'
        }
      },
      {
        path: 'instances-list',
        component: InstancesListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Instances List',
          pageName: 'instancesList'
        }
      },
      {
        path: 'allocations',
        component: AllocationsComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Allocations',
          pageName: 'allocations'
        }
      },
      {
        path: 'not-allocated-list',
        component: AllocationsListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Not Allocated List',
          pageName: 'notAllocatedList'
        }
      },
      {
        path: 'allocated-list',
        component: AllocatedListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Allocated List',
          pageName: 'allocatedList'
        }
      },
      {
        path: 'delivered-list',
        component: DeliveredListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Delivered List',
          pageName: 'deliveredList'
        }
      },
      {
        path: 'yards',
        component: YardsComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Yards',
          pageName: 'yards'
        }
      },
      {
        path: 'destinations',
        component: DestinationsComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Destinations',
          pageName: 'destinations'
        }
      },
      {
        path: 'users-list',
        component: UsersListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Users List',
          pageName: 'usersList'
        }
      },
      {
        path: 'yards',
        component: YardsComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Usage Timing Limits',
          pageName: 'yards'
        }
      },
      {
        path: 'data-usage-limits',
        component: DataUsageLimitsComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Data Usage Limits',
          pageName: 'dataUsageLimits'
        }
      },
      {
        path: 'general-settings',
        component: SettingsComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'General Settings',
          pageName: 'generalSettings'
        }
      },
      {
        path: 'wifi-user-settings',
        component: WifiUserSettingsComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'WiFi User Settings',
          pageName: 'wifiUserSettings'
        }
      },
      {
        path: 'access-point-manager',
        component: AccessPointManagerComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Access Point Manager',
          pageName: 'accessPointManager'
        }
      },
      {
        path: 'access-point-managers-list',
        component: AccessPointManagerListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Access Point Manager List',
          pageName: 'accessPointManagerList'
        }
      },
      {
        path: 'web-logs',
        component: WebLogsComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Web Logs',
          pageName: 'webLog'
        }
      }
    ]
  }
];

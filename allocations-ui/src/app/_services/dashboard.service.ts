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
import { Injectable, NgZone } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { DefaultApiResponse } from '../_models';
import { AuthenticationService } from './authentication.service';
import { UtilityService } from './utility.service';

declare var EventSource;

@Injectable({
  providedIn: 'root'
})
export class DashboardService {

  private es: EventSource;

  constructor(
    private http: HttpClient,
    private authenticationService: AuthenticationService,
    private utilityService: UtilityService,
    private zone: NgZone) {
    }

  getInterfaces() {
    return this.http.get<DefaultApiResponse>(`${environment.apiUrl}/dashboard/getInterfaces`);
  }

  getDashboardData(): Observable<any> {
    return this.http.get<DefaultApiResponse>(`${environment.apiUrl}/dashboard`);
  }

  closeConnection() {
    this.es.close();
  }

  private getEventSource(url: string): EventSource {
    return new EventSource(url);
  }
}

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

import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Resolve } from '@angular/router';
import { environment } from '../../environments/environment';
import { AccessToken } from '../_models';
import { Router } from '@angular/router';
import * as _ from 'lodash';
import { Observable, BehaviorSubject } from 'rxjs';
import { DefaultApiResponse, UserInfo } from '../_models';

@Injectable({
  providedIn: 'root'
})
export class AuthenticationService implements Resolve<any> {

  public userInfo: BehaviorSubject<UserInfo>;

  constructor(private http: HttpClient, private router: Router) {
    this.userInfo = new BehaviorSubject<UserInfo>(null);
  }

  resolve() {
    return this.retrieveUserInfo();
  }

  login(username: string, password: string) {
    return this.http.post<any>(`${environment.apiUrl}/auth`, {username, password});
  }

  logout() {
    localStorage.removeItem(AccessToken);
    this.router.navigate(['/login']);
  }

  verifyPageAccess(pageName: string): Observable<any> {
    return this.http.get<any>(`${environment.apiUrl}/auth/verifyPageAccess/${pageName}`);
  }

  getAccessToken() {
    return localStorage.getItem(AccessToken);
  }

  removeAccessToken() {
    localStorage.removeItem(AccessToken);
  }

  setAccessToken(accessToken: string): void {
    if (accessToken) {
      localStorage.setItem(AccessToken, accessToken);
    }
  }

  retrieveUserInfo(): Observable<any> {
    return this.http.get(`${environment.apiUrl}/auth/getUserInfo`);
  }

  getUserInfo(): void {
    this.retrieveUserInfo()
      .subscribe((response: DefaultApiResponse) => this.publishUserInfo(response.data));
  }

  publishUserInfo(userInfo: UserInfo): void {
    this.userInfo.next(userInfo);
  }

  getMenuItems(): Observable<any> {
    return this.http.get(`${environment.apiUrl}/auth/getMenuItems`);
  }

  forgotPassword(postData: any): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/auth/forgotPassword`, postData);
  }
}

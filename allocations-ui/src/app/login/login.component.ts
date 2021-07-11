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

import { Component, OnInit, ViewChild, TemplateRef } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';
import { Title } from '@angular/platform-browser';
import { noop } from 'rxjs';
import { finalize } from 'rxjs/operators';
import { MatDialog, MatDialogRef } from '@angular/material';
import { AuthenticationService, SnackbarService } from '../_services';

declare interface LoginResponse {
  status: boolean;
  accessToken?: string;
  message: string;
}

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {

  public form: FormGroup;
  public forgotPasswordForm: FormGroup;
  public isFormLoading: boolean = false;
  public isForgotPasswordFormLoading: boolean = false;
  private returnUrl: string;
  private modalRef: MatDialogRef<TemplateRef<any>>;

  @ViewChild('forgotPasswordModal', {static: true}) forgotPasswordModal: TemplateRef<any>;
  constructor(
    private formBuilder: FormBuilder,
    private activatedRoute: ActivatedRoute,
    private router: Router,
    private titleService: Title,
    private dialog: MatDialog,
    private snackBar: SnackbarService,
    private authenticationService: AuthenticationService
  ) {
    if (this.authenticationService.getAccessToken()) {
      this.router.navigate(['/panel/dashboard']);
    }
  }

  ngOnInit(): void {
    this.titleService.setTitle('Allocations Login');
    this.form = this.formBuilder.group({
      username: new FormControl(null, [
        Validators.required
      ]),
      password: new FormControl(null, [
        Validators.required
      ]),
    });
    this.returnUrl = this.activatedRoute.snapshot.queryParams['returnUrl'] || '/panel/dashboard';
  }

  get f() { return this.form.controls; }

  public onForgotPasswordLinkClick(): void {
    this.modalRef = this.dialog.open(this.forgotPasswordModal);
    this.initPasswordForm();
  }

  private initPasswordForm() {
    this.forgotPasswordForm = this.formBuilder.group({
      username: new FormControl(null, [
        Validators.minLength(5),
        Validators.maxLength(35),
        Validators.pattern(/^\S*$/)
      ])
    });
  }
  
  onSubmit(): void {
    if (this.form.invalid) {
      return;
    }

    this.isFormLoading = true;
    this.authenticationService.login(this.f.username.value, btoa(this.f.password.value))
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: LoginResponse) => {
        if (response && response.status) {
          this.snackBar.show('Login Successfull', 'success');
          this.authenticationService.setAccessToken(response.accessToken);
          this.router.navigate([this.returnUrl]);
        } else {
          this.snackBar.show('Login unsuccessfull', 'danger');
        }
      }, () => noop());
  }

  onForgotPasswordFormSubmit() : void {
    if (this.forgotPasswordForm.invalid) {
      return;
    }

    this.isForgotPasswordFormLoading = true;
    this.authenticationService.forgotPassword(this.forgotPasswordForm.value)
      .pipe(finalize(() => {
        this.isForgotPasswordFormLoading = false;
        this.modalRef.close();
      }))
      .subscribe((response: LoginResponse) => {
        if (response) {
          this.snackBar.show(response.message, response.status ? 'success' : 'danger');
        }
      }, () => noop());
  }
}
